<?php
require_once("fpdf151/fpdf.php");
//define('FPDF_FONTPATH','D:/Program Files/Ariadne/lib/modules/fpdf151/font/');
define('FPDF_FONTPATH','D:/Program Files/Ariadne/lib/modules/fpdf151/font/');

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
    $this->FPDF($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
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

function SetAuthor($author)
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

var $B;
var $I;
var $U;
var $HREF;

function _WriteHTML($html)
{
	return $this->WriteHTML($html);
}

function WriteHTML($html)
{
    //HTML parser
    $html=str_replace("\n",' ',$html);
    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
    foreach($a as $i=>$e)
    {
        if($i%2==0)
        {
            //Text
            if($this->HREF)
                $this->PutLink($this->HREF,$e);
            else
                $this->Write(5,$e);
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
                    if(ereg('^([^=]*)=["\']?([^"\']*)["\']?$',$v,$a3))
                        $attr[strtoupper($a3[1])]=$a3[2];
                $this->OpenTag($tag,$attr);
            }
        }
    }
}

function _OpenTag($tag,$attr)
{
	return $this->OpenTag($tag,$attr);
}

function OpenTag($tag,$attr)
{
    //Opening tag
    if($tag=='STRONG')
        $tag='B';
    if($tag=='EM')
        $tag='I';
    if($tag=='B' or $tag=='I' or $tag=='U')
        $this->SetStyle($tag,true);
    if($tag=='A')
        $this->HREF=$attr['HREF'];
    if($tag=='BR')
        $this->Ln(5);
}

function _CloseTag($tag)
{
	return $this->CloseTag($tag);
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='STRONG')
        $tag='B';
    if ($tag=='EM')
        $tag='I';
    if($tag=='B' or $tag=='I' or $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
    if($tag=='P')
        $this->Ln(10);
}

function _SetStyle($tag,$enable)
{
	return $this->SetStyle($tag,$enable);
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

function _PutLink($URL,$txt)
{
	return $this->PutLink($URL,$txt);
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

?>