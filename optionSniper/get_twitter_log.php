<?php
$filename = __DIR__.'/optionSniper.log';
$output = shell_exec('exec tail -n50 ' . $filename);  //only print last 50 lines
echo str_replace(PHP_EOL, '<br />', $output);         //add newlines
?>