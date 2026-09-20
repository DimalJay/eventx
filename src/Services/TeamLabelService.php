<?php

namespace Services;

use Models\TeamLabel;

class TeamLabelService
{
    public function getLabels(int $eventId): string
    {
        $rows = TeamLabel::where(["eventId" => $eventId]);
        if (count($rows) === 0) {
            return "";
        }
        return trim($rows[0]["labels"] ?? "");
    }

    public function updateLabels(int $eventId, string $labels): void
    {
        $rows = TeamLabel::where(["eventId" => $eventId]);
        if (count($rows) === 0) {
            (new TeamLabel($eventId, $labels))->save();
            return;
        }
        TeamLabel::updateRecord(["eventId" => $eventId], ["labels" => $labels]);
    }
}