<?php
require("../init.php");
use Desmos\Models\DesmosGraph;

header('Content-Type: application/json');
echo  DesmosGraph::findGraph($_GET['id']);