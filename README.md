Php class to manipulate images iptc Datas
Read and write iptc tags in php to an image file.
see http://php.net/manual/fr/function.iptcembed.php

<?php
$iptc = new iptc('images/example.jpg');
$iptc->set('city', 'Meymac');
$iptc->write();

$datas = json_encode(array('x' => '400', 'y' => '200'));
$iptc = new iptc('images/example.jpg');
print $iptc->set('DocumentNotes');
$iptc->write();

