<?php
require_once("fpdf151/fpdf.php");
//define('FPDF_FONTPATH','D:/Program Files/Ariadne/lib/modules/fpdf151/font/');
define('FPDF_FONTPATH','font/');

class pinp_PDF {
	public static function _init($orientation='P',$unit='mm',$format='A4') {
		$pdf=new pinp_FPDF($orientation='P',$unit='mm',$format='A4');
		return $pdf;
	}
}

class pinp_FPDF extends FPDF
{

	public function __construct ($orientation='P',$unit='mm',$format='A4')
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

	public function _SetMargins($left,$top,$right=-1)
	{
		return $this->SetMargins($left,$top,$right);
	}

	public function _SetLeftMargin($margin)
	{
		return $this->SetLeftMargin($margin);
	}

	public function _SetTopMargin($margin)
	{
		return $this->SetTopMargin($margin);
	}

	public function _SetRightMargin($margin)
	{
		return $this->SetRightMargin($margin);
	}

	public function _SetAutoPageBreak($auto,$margin=0)
	{
		return $this->SetAutoPageBreak($auto,$margin);
	}

	public function _SetDisplayMode($zoom,$layout='continuous')
	{
		return $this->SetDisplayMode($zoom,$layout);
	}

	public function _SetCompression($compress)
	{
		return $this->SetCompression($compress);
	}

	public function _SetTitle($title)
	{
		return $this->SetTitle($title);
	}

	public function _SetSubject($subject)
	{
		return $this->SetSubject($subject);
	}

	public function _SetAuthor($author)
	{
		return $this->SetAuthor($author);
	}

	public function _SetKeywords($keywords)
	{
		return $this->SetKeywords($keywords);
	}

	public function _SetCreator($creator)
	{
		return $this->SetCreator($creator);
	}

	public function _AliasNbPages($alias='{nb}')
	{
		return $this->AliasNbPages($alias);
	}

	public function _Error($msg)
	{
		return $this->Eroor($msg);
	}

	public function _Open()
	{
		return $this->Open();
	}

	public function _Close()
	{
		return $this->Close();
	}

	public function _AddPage($orientation='')
	{
		return $this->AddPage($orientation);
	}

	public function _Header()
	{
		//To be implemented in your own inherited class
	}

	public function _Footer()
	{
		//To be implemented in your own inherited class
	}

	public function _PageNo()
	{
		return $this->PageNo();
	}

	public function _SetDrawColor($r,$g=-1,$b=-1)
	{
		return $this->SetDrawColor($r,$g,$b);
	}

	public function _SetFillColor($r,$g=-1,$b=-1)
	{
		return $this->SetFillColor($r,$g,$b);
	}

	public function _SetTextColor($r,$g=-1,$b=-1)
	{
		return $this->SetTextColor($r,$g,$b);
	}

	public function _GetStringWidth($s)
	{
		return $this->GetStringWidth($s);
	}

	public function _SetLineWidth($width)
	{
		return $this->SetLineWidth($width);
	}

	public function _Line($x1,$y1,$x2,$y2)
	{
		return $this->Line($x1,$y1,$x2,$y2);
	}

	public function _Rect($x,$y,$w,$h,$style='')
	{
		return $this->Rect($x,$y,$w,$h,$style);
	}

	public function _AddFont($family,$style='',$file='')
	{
		// FIXME: this is insecure, fonts should be gotten from
		// inside Ariadne
		return $this->AddFont($family,$style,$file);
	}

	public function _SetFont($family,$style='',$size=0)
	{
		return $this->SetFont($family,$style,$size);
	}

	public function _SetFontSize($size)
	{
		return $this->SetFontSize($size);
	}

	public function _AddLink()
	{
		return $this->AddLink();
	}

	public function _SetLink($link,$y=0,$page=-1)
	{
		return $this->SetLink($link,$y,$page);
	}

	public function _Link($x,$y,$w,$h,$link)
	{
		return $this->Link($x,$y,$w,$h,$link);
	}

	public function _Text($x,$y,$txt)
	{
		return $this->Text($x,$y,$txt);
	}

	public function _AcceptPageBreak()
	{
		return $this->AcceptPageBreak();
	}

	public function _Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
	{
		return $this->Cell($w,$h,$txt,$border,$ln,$align,$fill,$link);
	}

	public function _MultiCell($w,$h,$txt,$border=0,$align='J',$fill=0)
	{
		return $this->MultiCell($w,$h,$txt,$border,$align,$fill);
	}

	public function _Write($h,$txt,$link='')
	{
		return $this->Write($h,$txt,$link);
	}

	public function _Image($file,$x,$y,$w,$h=0,$type='',$link='')
	{
		return $this->Image($file,$x,$y,$w,$h,$type,$link);
	}

	public function _Ln($h='')
	{
		return $this->Ln($h);
	}

	public function _GetX()
	{
		return $this->GetX();
	}

	public function _SetX($x)
	{
		return $this->SetX($x);
	}

	public function _GetY()
	{
		return $this->GetY();
	}

	public function _SetY($y)
	{
		return $this->SetY($y);
	}

	public function _SetXY($x,$y)
	{
		return $this->SetXY($x,$y);
	}

	public function _Output($file='',$download=false)
	{
		return $this->Output($file,$download);
	}

	public function _WriteHTML($html)
	{
		return $this->WriteHTML($html);
	}

	public function _OpenTag($tag,$attr)
	{
		return $this->OpenTag($tag,$attr);
	}

	public function _CloseTag($tag)
	{
		return $this->CloseTag($tag);
	}

	public function _SetStyle($tag,$enable)
	{
		return $this->SetStyle($tag,$enable);
	}

	public function _PutLink($URL,$txt)
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
	public function hex2dec($color = "#000000"){
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
	public function px2mm($px){
		return $px*25.4/72;
	}

	public function txtentities($html){
		$trans = get_html_translation_table(HTML_ENTITIES);
		$trans = array_flip($trans);
		return strtr($html, $trans);
	}

	//variables of html parser
	protected $B;
	protected $I;
	protected $U;
	protected $HREF;
	protected $fontList;
	protected $issetfont;
	protected $issetcolor;

	public function WriteHTML($html)
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

	public function OpenTag($tag,$attr)
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
				if(isset($attr['SRC']) && (isset($attr['WIDTH']) || isset($attr['HEIGHT']))) {
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
				if (isset($attr['COLOR']) || $attr['COLOR']!='') {
					$coul=$this->hex2dec($attr['COLOR']);
					$this->SetTextColor($coul['R'],$coul['G'],$coul['B']);
					$this->issetcolor=true;
				}
				if (isset($attr['FACE']) || in_array(strtolower($attr['FACE']), $this->fontlist)) {
					$this->SetFont(strtolower($attr['FACE']));
					$this->issetfont=true;
				}
				break;
		}
	}

	public function CloseTag($tag)
	{
		//Closing tag
		if($tag=='STRONG')
			$tag='B';
		if($tag=='EM')
			$tag='I';
		if($tag=='B' || $tag=='I' || $tag=='U')
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

	public function SetStyle($tag,$enable)
	{
		//Modify style and select corresponding font
		$this->$tag+=($enable ? 1 : -1);
		$style='';
		foreach(array('B','I','U') as $s)
			if($this->$s>0)
				$style.=$s;
		$this->SetFont('',$style);
	}

	public function PutLink($URL,$txt)
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
