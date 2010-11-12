<?php

// Revision
$revision = '$Rev: 604 $';
if (preg_match("/Rev: ([0-9]+)/", $revision, $matches))
{
    define("SVN_VERSION", "(SVN rev. {$matches[1]})");
}else
{
    define("SVN_VERSION", "");
}
 
 
 
 
 
 
 
 
