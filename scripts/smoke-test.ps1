$ErrorActionPreference = "Stop"
$base = "http://localhost/eventx/api/v1"
$testEmail = "smoketest_" + (Get-Random -Maximum 99999) + "@eventx.test"
$testPass = "smoke-test-pass-123"
$adminEmail = "smokeadmin_" + (Get-Random -Maximum 99999) + "@eventx.test"
$adminPass = "smoke-admin-pass-123"

$tmpBody = Join-Path $env:TEMP ("body_" + (Get-Random) + ".txt")
$tmpJson = Join-Path $env:TEMP ("json_" + (Get-Random) + ".txt")

$results = [System.Collections.Generic.List[object]]::new()

function Add-Result {
    param($method, $path, $expected, $got, $note, $ok)
    $script:results.Add([pscustomobject]@{
        Method = $method; Path = $path; Expected = $expected; Got = $got
        Note = $note; OK = $ok
    })
}

function Invoke-Curl {
    param([string[]]$curlArgs)
    & curl.exe @curlArgs
}

# JSON helper: returns @{ Status; Body }
function Call-Web {
    param($method, $url, [string]$json, [string]$cookie, [string]$extraHeaders)
    Remove-Item $tmpBody,$tmpJson -ErrorAction SilentlyContinue
    $a = @("-s","-o",$tmpBody,"-w","%{http_code}","-X",$method,$url,"-m",20)
    if ($json) {
        [System.IO.File]::WriteAllText($tmpJson, $json, (New-Object System.Text.UTF8Encoding($false)))
        $a += @("-H","Content-Type: application/json","--data","@$tmpJson")
    }
    if ($cookie) { $a += @("-H","Cookie: $cookie") }
    if ($extraHeaders) { $a += @("-H",$extraHeaders) }
    $status = Invoke-Curl $a
    $body = Get-Content $tmpBody -Raw -ErrorAction SilentlyContinue
    $parsed = $null
    try { $parsed = $body | ConvertFrom-Json } catch { $parsed = $body }
    return @{ Status = [int]$status; Body = $parsed; Raw = $body }
}

# Form helper for POST /event which reads $_POST
function Call-Form {
    param($url, $fields, $cookie)
    Remove-Item $tmpBody -ErrorAction SilentlyContinue
    $a = @("-s","-o",$tmpBody,"-w","%{http_code}","-X","POST",$url,"-m",20)
    foreach ($k in $fields.Keys) { $a += @("-F","$k=$($fields[$k])") }
    if ($cookie) { $a += @("-H","Cookie: $cookie") }
    $status = Invoke-Curl $a
    $body = Get-Content $tmpBody -Raw -ErrorAction SilentlyContinue
    $parsed = $null
    try { $parsed = $body | ConvertFrom-Json } catch { $parsed = $body }
    return @{ Status = [int]$status; Body = $parsed; Raw = $body }
}

# ---------- SETUP ----------
Write-Host "=== SETUP: register + DB prepare ===" -ForegroundColor Cyan
$reg = Call-Web POST "$base/auth/register" (@{ email=$testEmail; password=$testPass; firstName="Smoke"; lastName="Test" } | ConvertTo-Json -Compress)
Add-Result "POST" "/auth/register" "200" $reg.Status $reg.Body.message ($reg.Status -eq 200)

$setupPhp = Join-Path $env:TEMP ("set_" + (Get-Random) + ".php")
@"
<?php
`$pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4','root','');
`$pdo->exec("UPDATE users SET isVerified=1 WHERE email='$testEmail'");
`$uid = `$pdo->query("SELECT id FROM users WHERE email='$testEmail'")->fetchColumn();
`$pdo->exec("INSERT INTO team_access (userId,eventId,role,status,joinedAt) VALUES (`$uid,1,'COORDINATOR','ACTIVE',NOW())");
`$adminHash = password_hash('$adminPass', PASSWORD_DEFAULT);
`$pdo->prepare("INSERT INTO admins (email,firstName,lastName,password,createdAt,updatedAt) VALUES (?, 'Smoke','Admin',? ,NOW(),NOW())")->execute(['$adminEmail', `$adminHash]);
`$aid = `$pdo->lastInsertId();
echo json_encode(['uid'=>(int)`$uid,'adminId'=>(int)`$aid]);
"@ | Set-Content -Path $setupPhp -Encoding ASCII
$setup = php $setupPhp
Remove-Item $setupPhp -ErrorAction SilentlyContinue
$setupObj = ($setup -join "") | ConvertFrom-Json
$testUid = $setupObj.uid
Write-Host "Prepared: uid=$testUid adminId=$($setupObj.adminId)"

$login = Call-Web POST "$base/auth/login" (@{ email=$testEmail; password=$testPass } | ConvertTo-Json -Compress)
Add-Result "POST" "/auth/login" "200" $login.Status $login.Body.message ($login.Status -eq 200 -and $login.Body.success)
$token = $login.Body.data.token
$cookie = "auth_token=$token"

# ---------- PUBLIC / UNAUTH ----------
Write-Host "`n=== PUBLIC/UNAUTH ROUTES ===" -ForegroundColor Cyan
$e = Call-Web GET "$base/event?id=1"
Add-Result "GET" "/event?id=1" "200" $e.Status ($(if($e.Body.success){"success"}else{"fail"})+"/"+$e.Body.message) ($e.Status -eq 200 -and $e.Body.success)

$d = Call-Web GET "$base/discover-events"
$dCount = if ($d.Body.data) { @($d.Body.data).Count } else { 0 }
Add-Result "GET" "/discover-events" "200" $d.Status ($dCount.ToString()+" events") ($d.Status -eq 200 -and $d.Body.success)

$f = Call-Web GET "$base/feedback?id=1"
Add-Result "GET" "/feedback?id=1" "200" $f.Status $f.Body.message ($f.Status -eq 200)

# ---------- PROTECTED WITHOUT AUTH (401) ----------
Write-Host "`n=== PROTECTED ROUTES WITHOUT AUTH (expect 401) ===" -ForegroundColor Cyan
$noAuth = @("/user","/user/registrations","/events","/event/registrations","/registrations",
            "/tasks","/task","/team-access","/feedbacks","/event/1/can-manage","/payment/connect-status")
foreach ($p in $noAuth) {
    $r = Call-Web GET ($base + $p)
    Add-Result "GET" $p "401" $r.Status $r.Body.message ($r.Status -eq 401)
}
$cronNo = Call-Web GET "$base/cron/reminders"
Add-Result "GET" "/cron/reminders" "401" $cronNo.Status $cronNo.Body.message ($cronNo.Status -eq 401)
$scanNo = Call-Web POST "$base/registration/scan" '{"ticketCode":"whatever"}'
Add-Result "POST" "/registration/scan" "401" $scanNo.Status $scanNo.Body.message ($scanNo.Status -eq 401)

# ---------- PROTECTED WITH AUTH ----------
Write-Host "`n=== PROTECTED ROUTES WITH AUTH ===" -ForegroundColor Cyan
if ($token) {
    $authRoutes = @(
        @{ p="/user"; q="" }, @{ p="/user/registrations"; q="" },
        @{ p="/events"; q="" },
        @{ p="/tasks"; q="?eventId=1" }, @{ p="/team-access"; q="?eventId=1" },
        @{ p="/feedbacks"; q="?eventId=1" }, @{ p="/event/1/can-manage"; q="" },
        @{ p="/payment/connect-status"; q="" }, @{ p="/event/registrations"; q="?eventId=1" }
    )
    foreach ($r in $authRoutes) {
        $resp = Call-Web GET ($base + $r.p + $r.q) "" $cookie
        $ok = $resp.Status -eq 200
        $ok = $ok -and ($resp.Body.success -ne $false) -or ($resp.Body.success -eq $false -and $resp.Body.message -like "*not*verif*")
        $note = if ($resp.Body.success -eq $true) { "success" } else { "note: " + $resp.Body.message }
        Add-Result "GET" ($r.p + $r.q) "200" $resp.Status $note $ok
    }
}

# ---------- ADMIN ROUTES ----------
Write-Host "`n=== ADMIN ROUTES ===" -ForegroundColor Cyan
$admLogin = Call-Web POST "$base/auth/admin-login" (@{ email=$adminEmail; password=$adminPass } | ConvertTo-Json -Compress)
Add-Result "POST" "/auth/admin-login" "200" $admLogin.Status $admLogin.Body.message ($admLogin.Status -eq 200 -and $admLogin.Body.success)
$admCookie = "auth_token=$($admLogin.Body.data.token)"
if ($admLogin.Body.data.token) {
    $stats = Call-Web GET "$base/admin/dashboard-stats" "" $admCookie
    Add-Result "GET" "/admin/dashboard-stats" "200" $stats.Status $stats.Body.message ($stats.Status -eq 200 -and $stats.Body.success)
    $acts = Call-Web GET "$base/admin/activities" "" $admCookie
    Add-Result "GET" "/admin/activities" "200" $acts.Status $acts.Body.message ($acts.Status -eq 200 -and $acts.Body.success)
}

# ---------- AUTH FLOWS ----------
Write-Host "`n=== AUTH FLOWS ===" -ForegroundColor Cyan
$rv = Call-Web POST "$base/auth/resend-verification" (@{ email=$testEmail } | ConvertTo-Json -Compress)
Add-Result "POST" "/auth/resend-verification" "200" $rv.Status $rv.Body.message ($rv.Status -eq 200)
$fp = Call-Web POST "$base/auth/forgot-password" (@{ email=$testEmail } | ConvertTo-Json -Compress)
Add-Result "POST" "/auth/forgot-password" "200" $fp.Status $fp.Body.message ($fp.Status -eq 200)
$lo = Call-Web POST "$base/auth/logout" "" $cookie
Add-Result "POST" "/auth/logout" "200" $lo.Status $lo.Body.message ($lo.Status -eq 200)

# ---------- MUTATIONS (self-cleaning) ----------
Write-Host "`n=== MUTATION ROUTES ===" -ForegroundColor Cyan
if ($token) {
    # Event create (form) -> then delete (cleanup)
    $evFields = @{ title="Smoke Test Event"; eventType="online"; description="smoke"; startDate="2026-12-01 09:00:00"; endDate="2026-12-01 17:00:00"; location="Test"; isPublic="1"; capacity="100" }
    $ev = Call-Form "$base/event" $evFields $cookie
    $evId = $ev.Body.data.id
    Add-Result "POST" "/event" "200" $ev.Status $ev.Body.message ($ev.Status -eq 200 -and $ev.Body.success)

    # Registrations list on the user's OWN event (user is organizer)
    $regs = Call-Web GET "$base/registrations?eventId=$evId" "" $cookie
    Add-Result "GET" "/registrations?eventId={own}" "200" $regs.Status $regs.Body.message ($regs.Status -eq 200 -and $regs.Body.success)

    # Task on the new event
    $tk = Call-Web POST "$base/task" (@{ eventId=$evId; title="Smoke task"; assignedTo=$testUid; assignedBy=$testUid; dueDate="2026-11-30 17:00:00" } | ConvertTo-Json -Compress) $cookie
    Add-Result "POST" "/task" "200" $tk.Status $tk.Body.message ($tk.Status -eq 200 -and $tk.Body.success)
    # Task API returns data=null, so resolve the created task id from DB
    $taskIdPhp = Join-Path $env:TEMP ("task_" + (Get-Random) + ".php")
    @"
<?php
`$pdo = new PDO('mysql:host=localhost;dbname=test;charset=utf8mb4','root','');
echo (int)`$pdo->query("SELECT id FROM tasks WHERE eventId=$evId ORDER BY id DESC LIMIT 1")->fetchColumn();
"@ | Set-Content -Path $taskIdPhp -Encoding ASCII
    $taskId = php $taskIdPhp
    Remove-Item $taskIdPhp -ErrorAction SilentlyContinue
    $tks = Call-Web PUT "$base/task/status" (@{ id=$taskId; status="in_progress" } | ConvertTo-Json -Compress) $cookie
    Add-Result "PUT" "/task/status" "200" $tks.Status $tks.Body.message ($tks.Status -eq 200 -and $tks.Body.success)
    $tkd = Call-Web DELETE "$base/task" (@{ id=$taskId } | ConvertTo-Json -Compress) $cookie
    Add-Result "DELETE" "/task" "200" $tkd.Status $tkd.Body.message ($tkd.Status -eq 200 -and $tkd.Body.success)

    # Team access add + remove
    $ta = Call-Web POST "$base/team-access" (@{ email=$testEmail; eventId=$evId; role="MEMBER" } | ConvertTo-Json -Compress) $cookie
    $taMemberId = $ta.Body.data.id
    Add-Result "POST" "/team-access" "200" $ta.Status $ta.Body.message ($ta.Status -eq 200 -and $ta.Body.success)
    if ($taMemberId) {
        $tad = Call-Web DELETE "$base/team-access" (@{ id=$taMemberId } | ConvertTo-Json -Compress) $cookie
        Add-Result "DELETE" "/team-access" "200" $tad.Status $tad.Body.message ($tad.Status -eq 200 -and $tad.Body.success)
    }

    # Join event + update status + scan
    $je = Call-Web POST "$base/join-event" (@{ email=$testEmail; eventId=$evId; firstName="Smoke"; lastName="Test" } | ConvertTo-Json -Compress)
    Add-Result "POST" "/join-event" "200" $je.Status $je.Body.message ($je.Status -eq 200 -and $je.Body.success)
    $regId = $je.Body.data.id
    if ($regId) {
        $rs = Call-Web PUT "$base/registration/status" (@{ id=$regId; status="attended" } | ConvertTo-Json -Compress) $cookie
        Add-Result "PUT" "/registration/status" "200" $rs.Status $rs.Body.message ($rs.Status -eq 200 -and $rs.Body.success)
    }
    $scan = Call-Web POST "$base/registration/scan" (@{ ticketCode=$je.Body.data.ticketCode } | ConvertTo-Json -Compress) $cookie
    Add-Result "POST" "/registration/scan" "200" $scan.Status $scan.Body.message ($scan.Status -eq 200 -and $scan.Body.success)

    # Feedback submit
    $fb = Call-Web POST "$base/feedback" (@{ eventId=$evId; participantId=$testUid; organizationRating=5; contentRating=5; experienceRating=5; comment="smoke feedback" } | ConvertTo-Json -Compress)
    Add-Result "POST" "/feedback" "200" $fb.Status $fb.Body.message ($fb.Status -eq 200 -and $fb.Body.success)

    # Clean up event
    $deld = Call-Web DELETE "$base/event/$evId" "" $cookie
    Add-Result "DELETE" "/event/{id}" "200" $deld.Status $deld.Body.message ($deld.Status -eq 200 -and $deld.Body.success)
}

Remove-Item $tmpBody,$tmpJson -ErrorAction SilentlyContinue

Write-Host "`n=== SUMMARY ===" -ForegroundColor Cyan
Write-Host ("{0,-4} {1,-5} {2,-38} {3,-12} {4,-7} {5}" -f "#","M","Path","Expected","Got","OK")
$pass = 0; $fail = 0; $i = 0
foreach ($r in $results) {
    $i++
    if ($r.OK) { $pass++ } else { $fail++ }
    $mark = if ($r.OK) { "PASS" } else { "FAIL" }
    $color = if ($r.OK) { "Green" } else { "Red" }
    Write-Host ("{0,-4} {1,-5} {2,-38} {3,-12} {4,-7} {5}" -f $i,$r.Method,$r.Path,$r.Expected,$r.Got,$mark) -ForegroundColor $color
    if (-not $r.OK) { Write-Host ("        -> " + $r.Note) -ForegroundColor Gray }
}
Write-Host "`nPass: $pass  Fail: $fail" -ForegroundColor $(if($fail -eq 0){"Green"}else{"Yellow"})