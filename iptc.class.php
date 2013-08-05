<?
/**
 * @see http://php.net/manual/fr/function.iptcembed.php
 *
 */

/************************************************************\

  IPTC EASY 1.0 - IPTC data manipulator for JPEG images

  All reserved www.image-host-script.com

  Sep 15, 2008

\************************************************************/

class iptc {

  protected $meta = array();
  protected $hasmeta = false;
  protected $file = false;

  protected $iptcTags = array(
    'ObjectName'=> '005',
    'EditStatus'=> '007',
    'Priority'=> '010',
    'Category'=> '015',
    'SupplementalCategory'=> '020',
    'FixtureIdentifier'=> '022',
    'Keywords'=> '025',
    'ReleaseDate'=> '030',
    'ReleaseTime'=> '035',
    'SpecialInstructions'=> '040',
    'ReferenceService'=> '045',
    'ReferenceDate'=> '047',
    'ReferenceNumber'=> '050',
    'CreatedDate'=> '055',
    'CreatedTime'=> '060',
    'OriginatingProgram'=> '065',
    'ProgramVersion'=> '070',
    'ObjectCycle'=> '075',
    'Cyline'=> '080',
    'CylineTitle'=> '085',
    'City'=> '090',
    'ProvinceState'=> '095',
    'CountryCode'=> '100',
    'Country'=> '101',
    'CriginalTransmissionReference'=> '103',
    'Ceadline'=> '105',
    'Credit'=> '110',
    'Source'=> '115',
    'CopyrightString'=> '116',
    'Caption'=> '120',
    'LocalCaption'=> '121',
 	  'DocumentNotes' => '230',
  );

  public function __construct($filename) {
    $size = getimagesize($filename, $info);
    $this->hasmeta = isset($info["APP13"]);
    if($this->hasmeta) {
      $this->meta = iptcparse ($info["APP13"]);
    }
    $this->file = $filename;
  }

  public function set($tag, $data) {
    $tag = $this->iptcTags[$tag];
    $this->meta["2#$tag"]= array($data);
    $this->hasmeta = true;
  }

  public function get($tag) {
    return isset($this->meta["2#$tag"]) ? $this->meta["2#$tag"][0] : false;
  }

  public function dump() {
    echo '<pre>';
    print_r($this->meta);
    echo '</pre>';
  }

  protected function binary() {
    $iptc_new = '';
    foreach (array_keys($this->meta) as $s) {
      $tag = str_replace("2#", "", $s);
      $iptc_new .= $this->iptc_maketag(2, $tag, $this->meta[$s][0]);
    }       
    return $iptc_new;   
  }

  protected function iptc_maketag($rec,$dat,$val) {
    $len = strlen($val);
    if ($len < 0x8000) {
      return chr(0x1c).chr($rec).chr($dat).
        chr($len >> 8).
        chr($len & 0xff).
        $val;
    } else {
      return chr(0x1c).chr($rec).chr($dat).
        chr(0x80).chr(0x04).
        chr(($len >> 24) & 0xff).
        chr(($len >> 16) & 0xff).
        chr(($len >> 8 ) & 0xff).
        chr(($len ) & 0xff).
        $val;
    }
  }

  public function write() {
    if(!function_exists('iptcembed')) return false;
    $mode = 0;
    $content = iptcembed($this->binary(), $this->file, $mode);   
    $filename = $this->file;
    unlink($filename); #delete if exists
    $fp = fopen($filename, "w");
    fwrite($fp, $content);
    fclose($fp);
  }   

  #requires GD library installed
  function removeAllTags() {
    $this->hasmeta = false;
    $this->meta = array();
    $img = imagecreatefromstring(implode(file($this->file)));
    unlink($this->file); #delete if exists
    imagejpeg($img,$this->file,100);
  }

};

