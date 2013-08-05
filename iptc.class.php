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
    'object_name'=> '005',
    'edit_status'=> '007',
    'priority'=> '010',
    'category'=> '015',
    'supplemental_category'=> '020',
    'fixture_identifier'=> '022',
    'keywords'=> '025',
    'release_date'=> '030',
    'release_time'=> '035',
    'special_instructions'=> '040',
    'reference_service'=> '045',
    'reference_date'=> '047',
    'reference_number'=> '050',
    'created_date'=> '055',
    'created_time'=> '060',
    'originating_program'=> '065',
    'program_version'=> '070',
    'object_cycle'=> '075',
    'byline'=> '080',
    'byline_title'=> '085',
    'city'=> '090',
    'province_state'=> '095',
    'country_code'=> '100',
    'country'=> '101',
    'original_transmission_reference'=> '103',
    'headline'=> '105',
    'credit'=> '110',
    'source'=> '115',
    'copyright_string'=> '116',
    'caption'=> '120',
    'local_caption'=> '121',
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

