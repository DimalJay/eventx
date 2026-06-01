<?php

require_once 'models/User.php';

use models\Book;

$user = new Book("dimal", 13);
$user->createClass();