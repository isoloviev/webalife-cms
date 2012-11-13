<?
/*
Powered by MKS Engine (c) 2006
Created: Ivan S. Soloviev, ivan@mk-studio.ru
Description: Translator from rus to lat OR lat to rus
*/

class translit {
	# Задаём функцию перекодировки кириллицы в транслит.
	function rus2lat ($string) 
	{
		$string = ereg_replace("ж","zh",$string);
		$string = ereg_replace("ё","yo",$string);
		$string = ereg_replace("й","i",$string);
		$string = ereg_replace("ю","yu",$string);
		$string = ereg_replace("ь","'",$string);
		$string = ereg_replace("ч","ch",$string);
		$string = ereg_replace("щ","sh",$string);
		$string = ereg_replace("ц","c",$string);
		$string = ereg_replace("у","u",$string);
		$string = ereg_replace("к","k",$string);
		$string = ereg_replace("е","e",$string);
		$string = ereg_replace("н","n",$string);
		$string = ereg_replace("г","g",$string);
		$string = ereg_replace("ш","sh",$string);
		$string = ereg_replace("з","z",$string);
		$string = ereg_replace("х","h",$string);
		$string = ereg_replace("ъ","''",$string);
		$string = ereg_replace("ф","f",$string);
		$string = ereg_replace("ы","y",$string);
		$string = ereg_replace("в","v",$string);
		$string = ereg_replace("а","a",$string);
		$string = ereg_replace("п","p",$string);
		$string = ereg_replace("р","r",$string);
		$string = ereg_replace("о","o",$string);
		$string = ereg_replace("л","l",$string);
		$string = ereg_replace("д","d",$string);
		$string = ereg_replace("э","yе",$string);
		$string = ereg_replace("я","jа",$string);
		$string = ereg_replace("с","s",$string);
		$string = ereg_replace("м","m",$string);
		$string = ereg_replace("и","i",$string);
		$string = ereg_replace("т","t",$string);
		$string = ereg_replace("б","b",$string);
		$string = ereg_replace("Ё","yo",$string);
		$string = ereg_replace("Й","I",$string);
		$string = ereg_replace("Ю","YU",$string);
		$string = ereg_replace("Ч","CH",$string);
		$string = ereg_replace("Ь","'",$string);
		$string = ereg_replace("Щ","SH'",$string);
		$string = ereg_replace("Ц","C",$string);
		$string = ereg_replace("У","U",$string);
		$string = ereg_replace("К","K",$string);
		$string = ereg_replace("Е","E",$string);
		$string = ereg_replace("Н","N",$string);
		$string = ereg_replace("Г","G",$string);
		$string = ereg_replace("Ш","SH",$string);
		$string = ereg_replace("З","Z",$string);
		$string = ereg_replace("Х","H",$string);
		$string = ereg_replace("Ъ","''",$string);
		$string = ereg_replace("Ф","F",$string);
		$string = ereg_replace("Ы","Y",$string);
		$string = ereg_replace("В","V",$string);
		$string = ereg_replace("А","A",$string);
		$string = ereg_replace("П","P",$string);
		$string = ereg_replace("Р","R",$string);
		$string = ereg_replace("О","O",$string);
		$string = ereg_replace("Л","L",$string);
		$string = ereg_replace("Д","D",$string);
		$string = ereg_replace("Ж","Zh",$string);
		$string = ereg_replace("Э","Ye",$string);
		$string = ereg_replace("Я","Ja",$string);
		$string = ereg_replace("С","S",$string);
		$string = ereg_replace("М","M",$string);
		$string = ereg_replace("И","I",$string);
		$string = ereg_replace("Т","T",$string);
		$string = ereg_replace("Б","B",$string);
		return $string;
	}

	# Теперь задаём функцию перекодировки транслита в кириллицу.
	function lat2rus ($string) 
	{
		$string = ereg_replace("zh","ж",$string);
		$string = ereg_replace("Zh","Ж",$string);
		$string = ereg_replace("yo","ё",$string);
		$string = ereg_replace("Yu","Ю",$string);
		$string = ereg_replace("Ju","Ю",$string);
		$string = ereg_replace("ju","ю",$string);
		$string = ereg_replace("yu","ю",$string);
		$string = ereg_replace("sh","ш",$string);
		$string = ereg_replace("yе","э",$string);
		$string = ereg_replace("jа","я",$string);
		$string = ereg_replace("yа","я",$string);
		$string = ereg_replace("Sh","Ш",$string);
		$string = ereg_replace("Ch","Ч",$string);
		$string = ereg_replace("ch","ч",$string);
		$string = ereg_replace("Yo","Ё",$string);
		$string = ereg_replace("Ya","Я",$string);
		$string = ereg_replace("Ja","Я",$string);
		$string = ereg_replace("Ye","Э",$string);
		$string = ereg_replace("i","и",$string);
		$string = ereg_replace("'","ь",$string);
		$string = ereg_replace("c","ц",$string);
		$string = ereg_replace("u","у",$string);
		$string = ereg_replace("k","к",$string);
		$string = ereg_replace("e","е",$string);
		$string = ereg_replace("n","н",$string);
		$string = ereg_replace("g","г",$string);
		$string = ereg_replace("z","з",$string);
		$string = ereg_replace("h","х",$string);
		$string = ereg_replace("''","ъ",$string);
		$string = ereg_replace("f","ф",$string);
		$string = ereg_replace("y","ы",$string);
		$string = ereg_replace("v","в",$string);
		$string = ereg_replace("a","а",$string);
		$string = ereg_replace("p","п",$string);
		$string = ereg_replace("r","p",$string);
		$string = ereg_replace("o","о",$string);
		$string = ereg_replace("l","л",$string);
		$string = ereg_replace("d","д",$string);
		$string = ereg_replace("s","с",$string);
		$string = ereg_replace("m","м",$string);
		$string = ereg_replace("t","т",$string);
		$string = ereg_replace("b","б",$string);
		$string = ereg_replace("I","Й",$string);
		$string = ereg_replace("'","Ь",$string);
		$string = ereg_replace("C","Ц",$string);
		$string = ereg_replace("U","У",$string);
		$string = ereg_replace("K","К",$string);
		$string = ereg_replace("E","Е",$string);
		$string = ereg_replace("N","Н",$string);
		$string = ereg_replace("G","Г",$string);
		$string = ereg_replace("Z","З",$string);
		$string = ereg_replace("H","Х",$string);
		$string = ereg_replace("''","Ъ",$string);
		$string = ereg_replace("F","Ф",$string);
		$string = ereg_replace("Y","Ы",$string);
		$string = ereg_replace("V","В",$string);
		$string = ereg_replace("A","А",$string);
		$string = ereg_replace("P","П",$string);
		$string = ereg_replace("R","Р",$string);
		$string = ereg_replace("O","О",$string);
		$string = ereg_replace("L","Л",$string);
		$string = ereg_replace("D","Д",$string);
		$string = ereg_replace("S","С",$string);
		$string = ereg_replace("M","М",$string);
		$string = ereg_replace("I","И",$string);
		$string = ereg_replace("T","Т",$string);
		$string = ereg_replace("B","Б",$string);
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