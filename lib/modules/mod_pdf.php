<?php
require_once("fpdf151/fpdf.php");
//define('FPDF_FONTPATH','D:/Program Files/Ariadne/lib/modules/fpdf151/font/');
define('FPDF_FONTPATH','font/');

class pinp_PDF {
	function _init($orientation='P',$unit='mm',$format='A4') {
		$pdf=new pinp_FPDF($orientation='P',$unit='mm',$format='A4');
		return $pdf;
	}
}

class pinp_FPDF extends FPDF
{

function pinp_FPDF($orientation='P',$unit='mm',$format='A4')
{
    //Call parent constructor
    $this->FPDF($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
    $this->fontlist=array("arial","times","courier","helvetica","symbol");
    $this->issetfont=false;
    $this->issetcolor=false;
}

function _SetMargins($left,$top,$right=-1)
{
	return $this->SetMargins($left,$top,$right);
}

function _SetLeftMargin($margin)
{
	return $this->SetLeftMargin($margin);
}

function _SetTopMargin($margin)
{
	return $this->SetTopMargin($margin);
}

function _SetRightMargin($margin)
{
	return $this->SetRightMargin($margin);
}

function _SetAutoPageBreak($auto,$margin=0)
{
	return $this->SetAutoPageBreak($auto,$margin);
}

function _SetDisplayMode($zoom,$layout='continuous')
{
	return $this->SetDisplayMode($zoom,$layout);
}

function _SetCompression($compress)
{
	return $this->SetCompression($compress);
}

function _SetTitle($title)
{
	return $this->SetTitle($title);
}

function _SetSubject($subject)
{
	return $this->SetSubject($subject);
}

function _SetAuthor($author)
{
	return $this->SetAuthor($author);
}

function _SetKeywords($keywords)
{
	return $this->SetKeywords($keywords);
}

function _SetCreator($creator)
{
	return $this->SetCreator($creator);
}

function _AliasNbPages($alias='{nb}')
{
	return $this->AliasNbPages($alias);
}

function _Error($msg)
{
	return $this->Eroor($msg);
}

function _Open()
{
	return $this->Open();
}

function _Close()
{
	return $this->Close();
}

function _AddPage($orientation='')
{
	return $this->AddPage($orientation);
}

function _Header()
{
	//To be implemented in your own inherited class
}

function _Footer()
{
	//To be implemented in your own inherited class
}

function _PageNo()
{
	return $this->PageNo();
}

function _SetDrawColor($r,$g=-1,$b=-1)
{
	return $this->SetDrawColor($r,$g,$b);
}

function _SetFillColor($r,$g=-1,$b=-1)
{
	return $this->SetFillColor($r,$g,$b);
}

function _SetTextColor($r,$g=-1,$b=-1)
{
	return $this->SetTextColor($r,$g,$b);
}

function _GetStringWidth($s)
{
	return $this->GetStringWidth($s);
}

function _SetLineWidth($width)
{
	return $this->SetLineWidth($width);
}

function _Line($x1,$y1,$x2,$y2)
{
	return $this->Line($x1,$y1,$x2,$y2);
}

function _Rect($x,$y,$w,$h,$style='')
{
	return $this->Rect($x,$y,$w,$h,$style);
}

function _AddFont($family,$style='',$file='')
{
	// FIXME: this is insecure, fonts should be gotten from
	// inside Ariadne
	return $this->AddFont($family,$style,$file);
}

function _SetFont($family,$style='',$size=0)
{
	return $this->SetFont($family,$style,$size);
}

function _SetFontSize($size)
{
	return $this->SetFontSize($size);
}

function _AddLink()
{
	return $this->AddLink();
}

function _SetLink($link,$y=0,$page=-1)
{
	return $this->SetLink($link,$y,$page);
}

function _Link($x,$y,$w,$h,$link)
{
	return $this->Link($x,$y,$w,$h,$link);
}

function _Text($x,$y,$txt)
{
	return $this->Text($x,$y,$txt);
}

function _AcceptPageBreak()
{
	return $this->AcceptPageBreak();
}

function _Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
{
	return $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
}

function _MultiCell($w,$h,$txt,$border=0,$align='J',$fill=0)
{
	return $this->MultiCell($w,$h,$txt,$border,$align,$fill);
}

function _Write($h,$txt,$link='')
{
	return $this->Write($h,$txt,$link);
}

function _Image($file,$x,$y,$w,$h=0,$type='',$link='')
{
	return $this->Image($file,$x,$y,$w,$h,$type,$link);
}

function _Ln($h='')
{
	return $this->Ln($h);
}

function _GetX()
{
	return $this->GetX();
}

function _SetX($x)
{
	return $this->SetX($x);
}

function _GetY()
{
	return $this->GetY();
}

function _SetY($y)
{
	return $this->SetY($y);
}

function _SetXY($x,$y)
{
	return $this->SetXY($x,$y);
}

function _Output($file='',$download=false)
{
	return $this->Output($file,$download);
}

function _WriteHTML($html)
{
	return $this->WriteHTML($html);
}

function _OpenTag($tag,$attr)
{
	return $this->OpenTag($tag,$attr);
}

function _CloseTag($tag)
{
	return $this->CloseTag($tag);
}

function _SetStyle($tag,$enable)
{
	return $this->SetStyle($tag,$enable);
}

function _PutLink($URL,$txt)
{
	return $this->PutLink($URL,$txt);
}

// code originally from HTML2PDF by Clément Lavoillotte
// ac.lavoillotte@noos.fr
// webmaster@streetpc.tk
// http://www.streetpc.tk

// function hex2dec
// returns an associative array (keys: R,G,B) from
// a hex html code (e.g. #3FE5AA)
function hex2dec($color = "#000000"){
    $R = substr($color, 1, 2);
    $rouge = hexdec($R);
    $V = substr($color, 3, 2);
    $vert = hexdec($V);
    $B = substr($color, 5, 2);
    $bleu = hexdec($B);
    $tbl_color = array();
    $tbl_color['R']=$rouge;
    $tbl_color['G']=$vert;
    $tbl_color['B']=$bleu;
    return $tbl_color;
}

// conversion pixel -> millimeter in 72 dpi
function px2mm($px){
    return $px*25.4/72;
}

function txtentities($html){
    $trans = get_html_translation_table(HTML_ENTITIES);
    $trans = array_flip($trans);
    return strtr($html, $trans);
}

//variables of html parser
var $B;
var $I;
var $U;
var $HREF;
var $fontList;
var $issetfont;
var $issetcolor;

function WriteHTML($html)
{
	require_once('mod_unicode.php');
	$html=unicode::utf8toiso8859($html);
    $html=strip_tags($html,"<b><u><i><a><img><p><br><strong><em><font><tr><blockquote>"); //remove all unsupported tags
    $html=str_replace("\n",' ',$html); //replace carriage returns by spaces
    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE); //explodes the string
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,stripslashes($this->txtentities($e)));
        }
        else
        {
            //Tag
            if($e{0}=='/')
                $this->CloseTag(strtoupper(substr($e,1)));
            else
            {
                //Extract attributes
                $a2=explode(' ',$e);
                $tag=strtoupper(array_shift($a2));
                $attr=array();
                foreach($a2 as $v)
                    if(preg_match('/^([^=]*)=["\']?([^"\']*)["\']?$/',$v,$a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                $this->OpenTag($tag,$attr);
            }
        }
    }
}

function OpenTag($tag,$attr)
{
    //Opening tag
    switch($tag){
        case 'STRONG':
            $this->SetStyle('B',true);
            break;
        case 'EM':
            $this->SetStyle('I',true);
            break;
        case 'B':
        case 'I':
        case 'U':
            $this->SetStyle($tag,true);
            break;
        case 'A':
            $this->HREF=$attr['HREF'];
            break;
        case 'IMG':
			if ($attr['SRC'] && substr($attr['SRC'], -1)=='/') {
				$attr['SRC']=substr($attr['SRC'], 0, -1);
			}
			// FIXME: make the image available as a file.
			// FIXME: remove width or height requirement.
            if(isset($attr['SRC']) and (isset($attr['WIDTH']) or isset($attr['HEIGHT']))) {
                if(!isset($attr['WIDTH']))
                    $attr['WIDTH'] = 0;
                if(!isset($attr['HEIGHT']))
                    $attr['HEIGHT'] = 0;
                $this->Image($attr['SRC'], $this->GetX(), $this->GetY(), $this->px2mm($attr['WIDTH']), $this->px2mm($attr['HEIGHT']));
            }
            break;
        case 'TR':
        case 'BLOCKQUOTE':
        case 'BR':
            $this->Ln(5);
            break;
        case 'P':
            $this->Ln(10);
            break;
        case 'FONT':
            if (isset($attr['COLOR']) and $attr['COLOR']!='') {
                $coul=$this->hex2dec($attr['COLOR']);
                $this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
                $this->issetcolor=true;
            }
            if (isset($attr['FACE']) and in_array(strtolower($attr['FACE']), $this->fontlist)) {
                $this->SetFont(strtolower($attr['FACE']));
                $this->issetfont=true;
            }
            break;
    }
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='STRONG')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' or $tag=='I' or $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
    if($tag=='FONT'){
        if ($this->issetcolor==true) {
            $this->SetTextColor(0);
        }
        if ($this->issetfont) {
            $this->SetFont('arial');
            $this->issetfont=false;
        }
    }
}

function SetStyle($tag,$enable)
{
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B','I','U') as $s)
        if($this->$s>0)
            $style.=$s;
    $this->SetFont('',$style);
}

function PutLink($URL,$txt)
{
    //Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}

//End of class
}
