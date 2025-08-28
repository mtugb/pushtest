<?php
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!isset($input['action'])) {
    exit("missing action prop");
}

