<?
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, ivan@mk-studio.ru
Description: Translator from rus to lat OR lat to rus
*/

class translit {
	# ����� ������� ������������� ��������� � ��������.
	function rus2lat ($string) 
	{
		$string = ereg_replace("�","zh",$string);
		$string = ereg_replace("�","yo",$string);
		$string = ereg_replace("�","i",$string);
		$string = ereg_replace("�","yu",$string);
		$string = ereg_replace("�","'",$string);
		$string = ereg_replace("�","ch",$string);
		$string = ereg_replace("�","sh",$string);
		$string = ereg_replace("�","c",$string);
		$string = ereg_replace("�","u",$string);
		$string = ereg_replace("�","k",$string);
		$string = ereg_replace("�","e",$string);
		$string = ereg_replace("�","n",$string);
		$string = ereg_replace("�","g",$string);
		$string = ereg_replace("�","sh",$string);
		$string = ereg_replace("�","z",$string);
		$string = ereg_replace("�","h",$string);
		$string = ereg_replace("�","''",$string);
		$string = ereg_replace("�","f",$string);
		$string = ereg_replace("�","y",$string);
		$string = ereg_replace("�","v",$string);
		$string = ereg_replace("�","a",$string);
		$string = ereg_replace("�","p",$string);
		$string = ereg_replace("�","r",$string);
		$string = ereg_replace("�","o",$string);
		$string = ereg_replace("�","l",$string);
		$string = ereg_replace("�","d",$string);
		$string = ereg_replace("�","y�",$string);
		$string = ereg_replace("�","j�",$string);
		$string = ereg_replace("�","s",$string);
		$string = ereg_replace("�","m",$string);
		$string = ereg_replace("�","i",$string);
		$string = ereg_replace("�","t",$string);
		$string = ereg_replace("�","b",$string);
		$string = ereg_replace("�","yo",$string);
		$string = ereg_replace("�","I",$string);
		$string = ereg_replace("�","YU",$string);
		$string = ereg_replace("�","CH",$string);
		$string = ereg_replace("�","'",$string);
		$string = ereg_replace("�","SH'",$string);
		$string = ereg_replace("�","C",$string);
		$string = ereg_replace("�","U",$string);
		$string = ereg_replace("�","K",$string);
		$string = ereg_replace("�","E",$string);
		$string = ereg_replace("�","N",$string);
		$string = ereg_replace("�","G",$string);
		$string = ereg_replace("�","SH",$string);
		$string = ereg_replace("�","Z",$string);
		$string = ereg_replace("�","H",$string);
		$string = ereg_replace("�","''",$string);
		$string = ereg_replace("�","F",$string);
		$string = ereg_replace("�","Y",$string);
		$string = ereg_replace("�","V",$string);
		$string = ereg_replace("�","A",$string);
		$string = ereg_replace("�","P",$string);
		$string = ereg_replace("�","R",$string);
		$string = ereg_replace("�","O",$string);
		$string = ereg_replace("�","L",$string);
		$string = ereg_replace("�","D",$string);
		$string = ereg_replace("�","Zh",$string);
		$string = ereg_replace("�","Ye",$string);
		$string = ereg_replace("�","Ja",$string);
		$string = ereg_replace("�","S",$string);
		$string = ereg_replace("�","M",$string);
		$string = ereg_replace("�","I",$string);
		$string = ereg_replace("�","T",$string);
		$string = ereg_replace("�","B",$string);
		return $string;
	}

	# ������ ����� ������� ������������� ��������� � ���������.
	function lat2rus ($string) 
	{
		$string = ereg_replace("zh","�",$string);
		$string = ereg_replace("Zh","�",$string);
		$string = ereg_replace("yo","�",$string);
		$string = ereg_replace("Yu","�",$string);
		$string = ereg_replace("Ju","�",$string);
		$string = ereg_replace("ju","�",$string);
		$string = ereg_replace("yu","�",$string);
		$string = ereg_replace("sh","�",$string);
		$string = ereg_replace("y�","�",$string);
		$string = ereg_replace("j�","�",$string);
		$string = ereg_replace("y�","�",$string);
		$string = ereg_replace("Sh","�",$string);
		$string = ereg_replace("Ch","�",$string);
		$string = ereg_replace("ch","�",$string);
		$string = ereg_replace("Yo","�",$string);
		$string = ereg_replace("Ya","�",$string);
		$string = ereg_replace("Ja","�",$string);
		$string = ereg_replace("Ye","�",$string);
		$string = ereg_replace("i","�",$string);
		$string = ereg_replace("'","�",$string);
		$string = ereg_replace("c","�",$string);
		$string = ereg_replace("u","�",$string);
		$string = ereg_replace("k","�",$string);
		$string = ereg_replace("e","�",$string);
		$string = ereg_replace("n","�",$string);
		$string = ereg_replace("g","�",$string);
		$string = ereg_replace("z","�",$string);
		$string = ereg_replace("h","�",$string);
		$string = ereg_replace("''","�",$string);
		$string = ereg_replace("f","�",$string);
		$string = ereg_replace("y","�",$string);
		$string = ereg_replace("v","�",$string);
		$string = ereg_replace("a","�",$string);
		$string = ereg_replace("p","�",$string);
		$string = ereg_replace("r","p",$string);
		$string = ereg_replace("o","�",$string);
		$string = ereg_replace("l","�",$string);
		$string = ereg_replace("d","�",$string);
		$string = ereg_replace("s","�",$string);
		$string = ereg_replace("m","�",$string);
		$string = ereg_replace("t","�",$string);
		$string = ereg_replace("b","�",$string);
		$string = ereg_replace("I","�",$string);
		$string = ereg_replace("'","�",$string);
		$string = ereg_replace("C","�",$string);
		$string = ereg_replace("U","�",$string);
		$string = ereg_replace("K","�",$string);
		$string = ereg_replace("E","�",$string);
		$string = ereg_replace("N","�",$string);
		$string = ereg_replace("G","�",$string);
		$string = ereg_replace("Z","�",$string);
		$string = ereg_replace("H","�",$string);
		$string = ereg_replace("''","�",$string);
		$string = ereg_replace("F","�",$string);
		$string = ereg_replace("Y","�",$string);
		$string = ereg_replace("V","�",$string);
		$string = ereg_replace("A","�",$string);
		$string = ereg_replace("P","�",$string);
		$string = ereg_replace("R","�",$string);
		$string = ereg_replace("O","�",$string);
		$string = ereg_replace("L","�",$string);
		$string = ereg_replace("D","�",$string);
		$string = ereg_replace("S","�",$string);
		$string = ereg_replace("M","�",$string);
		$string = ereg_replace("I","�",$string);
		$string = ereg_replace("T","�",$string);
		$string = ereg_replace("B","�",$string);
		return $string;
	}

	function utf8_win($string)
	{
		for ($c=0;$c<strlen($string);$c++){
			$i=ord($string[$c]);
			if ($i <= 127) @$out .= $string[$c];
				if (@$byte2){
					$new_c2=($c1&3)*64+($i&63);
					$new_c1=($c1>>2)&5;
					$new_i=$new_c1*256+$new_c2;
					if ($new_i==1025){
						$out_i=168;
					} else {
					   if ($new_i==1105){
							$out_i=184;
						} else {
							$out_i=$new_i-848;
						}
					}
					@$out .= chr($out_i);
					$byte2 = false;
				}
				if (($i>>5)==6) {
					$c1 = $i;
					$byte2 = true;
				}
		}
		return $out;
	}
} 


?>