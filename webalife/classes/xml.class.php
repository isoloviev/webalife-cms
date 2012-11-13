<?
/*
XML Document Generator
*/

class xml {
	var $Encode = "UTF-8"; // default xml encode
	var $XMLContent = "";
	/*
	Return XML Content
	*/
	function getXMLdoc($content, $save_to_server = false)
	{
		$string = "<?xml version=\"1.0\" encoding=\"".$this->Encode."\"?>\r\n";
		$this->parse_tree($content);
		if(!$save_to_server) {
			return $string.$this->XMLContent;
		}
	}
	
	/*
	Parse XML Structure 
	*/
	function parse_tree($text = array(), $r_cnt = 0)
	{
		global $r_cnt, $string;
		foreach($text as $key=>$value)
		{
			$params = split(";",$key);
			if(is_numeric($key)) { // если тэг повторяется несколько раз
				$this->parse_tree($value);
			} else {
				$string .= str_repeat(" ",$r_cnt)."<".implode(" ",$params).">";
				if(is_array($value)) { // $value - hash array;
						$r_cnt=$r_cnt+5;
						$string .= "\r\n";
						$this->parse_tree($value);
						$r_cnt=$r_cnt-5;
						$string .= str_repeat(" ",$r_cnt)."</".$params[0].">\r\n";
				} else {
						$string .= $value;
						$string .= "</".$params[0].">\r\n";
				}
			}
		}
		$this->XMLContent = $string;
	}
	
	/*
	Dump XML content
	*/
	function Dump()
	{
		echo "<div align=\"left\">";
		echo "<xmp>";
		echo "Dump XML Document...\r\n";
		echo $this->XMLContent;
		echo "</xmp>";
		echo "</div>";
	}
}
?>