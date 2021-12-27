<?php

namespace App\Service;

final class Util{
	public function numeroColor($array){
		
		$str_numeros = null;
		 		
 			foreach($array as $item){
 		
 				$clase = null; 			

 				switch($item){

 					case 1:
 						$clase = 'class="badge badge-pill bg-red text-dark" style="color:black"';
 					break;
 					
 					case 2:
 						$clase = 'class="badge badge-pill bg-white text-dark" style="background-color:#e5e5e5 !important"';
 					break;	

 					case 3:
 						$clase = 'class="badge badge-pill bg-blue text-dark" style="color:black"';
 					break;	
					
					case 4:
 						$clase = 'class="badge badge-pill bg-yellow text-dark" style="color:black"';
 					break;

 					case 5:
 						$clase ='class="badge badge-pill bg-green text-dark" style="color:black !important"';
 					break;

 					case 6:
 						$clase = 'class="badge badge-pill bg-black text-dark" style="color:black"';
 					break;

 					case 7:
 						$clase = 'class="badge badge-pill bg-orange text-dark" style="color:black"';
 					break;

 					case 8:
 						$clase = 'class="badge badge-pill bg-pink text-dark" style="color:black"';
 					break;

 					case 9:
 						$clase = 'class="badge badge-pill  text-dark" style=" background: rgba(0, 123, 255, 0.5) !important"';
 					break;

 					case 10:
 						$clase = 'class="badge badge-pill bg-purple text-dark" style="color:black"';
 					break;

 					case 11:
 						$clase = 'class="badge badge-pill bg-gray text-dark" style="color:red !important"';
 					break;

 					case 12:
 						$clase = 'class="badge badge-pill bg-lime  text-dark" ';
 					break;

 					case 13:
 						$clase = 'class="badge badge-pill  text-dark" style=" background: rgba(0, 123, 255, 0.7) !important"';
 					break;

 					case 14:
 						$clase = 'class="badge badge-pill bg-fuchsia  text-dark" ';
 					break;

 					case 15:
 						$clase = 'class="badge badge-pill  text-dark" style=" background: rgba(111, 66, 193, 0.6) !important"';
 					break; 					

 				}


 				$str_numeros .= '<span '.$clase.'>'.$item.'</span>';
            }





		return $str_numeros;

	}

}