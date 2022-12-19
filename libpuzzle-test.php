<?php

// http://www.bitchesofbp.com/1234567890/127/large/15206558-1.jpg
$file_loc1 = '127/large/15206558-3.jpg';
$file_loc2 = '214/large/9422893-1.jpg';
$file_loc2 = '127/large/15206558-3.jpg';

$filename1='/var/www/admin.blr.pw/html/cities_em/'.$file_loc1; ///cities-em/127/large/15206558-2.jpg
$filename2='/var/www/admin.blr.pw/html/cities_em/'.$file_loc2; // /cities-em/127/large/15206558-1.jpg

if(!file_exists($filename1)){
	print "file1 does not exist<br />";
	exit();
}
if(!file_exists($filename2)){
	print "file2 does not exist<br />";
	exit();
}

print "<img src='/cities_em/".$file_loc1."' />";
print "<img src='/cities_em/".$file_loc2."' />";

$signature1 = 	puzzle_fill_cvec_from_file($filename1);
$signature2 = puzzle_fill_cvec_from_file($filename2);

$d = puzzle_vector_normalized_distance($signature1, $signature2);
print "<hr>\n";
var_dump($d);
print "<hr>\n";
if ($d < PUZZLE_CVEC_SIMILARITY_THRESHOLD) {
  echo "Pictures look similar\n";
}else{
	echo "Pictures don't look similar\n";
}
?>