<?php
/**
 * @package HelloSanti
 * @version 1.0.3
 */
/*
Plugin Name: Hello Santi
GitHub URI: https://github.com/DrJoppix/hello-santi
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words of the famous Jesus Christ: Hello, Santi. When activated it will randomly insulte the saint of the day in the upper right of your admin screen on every page.
Author: DrJoppix
Version: 1.0.3
Author URI: https://github.com/DrJoppix
*/

define( 'HELLO_SANTI_FILE_URL', __FILE__ );
define( 'HELLO_SANTI_DIR', plugin_dir_path( __FILE__ ) );
define( 'HELLO_SANTI_URL', plugin_dir_url( __FILE__ ) );
define( 'HELLO_SANTI_VERSION', '1.0.2' );

define( 'HELLO_SANTI_URL_CSS', HELLO_SANTI_URL . 'src/css/' );

add_action( 'admin_enqueue_scripts', 'enqueue_scripts_dashboard' );
function enqueue_scripts_dashboard() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    wp_enqueue_style( 'hello-santi-style', HELLO_SANTI_URL_CSS . 'hello-santi.css', array(), HELLO_SANTI_VERSION, 'all' );
}

define( 'HELLO_SANTI_DEFAULT', array(
	"nome"=> "San Padre Pio",
	"tipologia"=> "Presbitero e religioso",
	"data"=> "25-05-1887",
	"default"=> "1",
	"permalink"=> "",
	"urlimmagine"=> "",
	"descrizione"=> "Truffatore. Cioè volevo dire bravissima persona che non ha affatto alimentato un business ridicolo. Curiosità: la sera si picchiava col Diavolo",
	) 
);

function hello_santi() {
	$chosen = hello_santi_get_burla();
	echo "<p id='dolly'>$chosen</p>";
}

add_action( 'admin_notices', 'hello_santi' );

function hello_santi_get_burla() {
	$response = wp_remote_get("https://www.santodelgiorno.it/santi.json");
	/**
	 * Restituise un array di santi e beati, ognuno che segue questa struttura:
	 * 
	 * nome
	 * tipologia
	 * data
	 * default
	 * permalink
	 * urlimmagine
	 * descrizione
	 */
	$datas = json_decode( $response['body'] );
	$index = mt_rand( 0, count( $datas ) - 1 );
	$santo = isset( $datas[$index] ) ? $datas[$index] : HELLO_SANTI_DEFAULT;
	$gender = get_santo_gender( $santo->nome );
	$burla = hello_santi_insulti_by_gender( $gender );
	return $santo->nome . ' ' .$burla;
}

function get_santo_gender( $nome ){
	$prefix = explode( " ", $nome );
	$prefix = isset( $prefix[0] ) ? $prefix[0] : 'San';

	switch ($prefix) {
		case 'Santa':
		case 'santa':
		case 'Beata':
		case 'beata':
		case 'Madonna':
		case 'madonna':
			return "F";
			break;

		case 'Sante':
		case 'sante':
		case 'Beate':
		case 'beate':
		case 'Madonne':
		case 'madonne':
			return "FF";
			break;

		case 'Santi':
		case 'santi':
		case 'Beati':
		case 'beati':
			return "MM";
			break;

		case 'San':
		case 'Santo':
		case 'santo':
		case 'Beato':
		case 'beato':
		case 'Natale':
		case 'Gesù':
		default:
			return "M";
			break;
	}
}

function hello_santi_insulti_by_gender( $gender ) {
	$santo = file_get_contents(HELLO_SANTI_DIR . 'burle/santo.txt');
	$santa = file_get_contents(HELLO_SANTI_DIR . 'burle/santa.txt');
	$santi = file_get_contents(HELLO_SANTI_DIR . 'burle/santi.txt');
	$sante = file_get_contents(HELLO_SANTI_DIR . 'burle/sante.txt');;

	switch ($gender) {
		case 'FF':
			$response = $sante;
			break;
		case 'F':
			$response = $santa;
			break;
		case 'MM':
			$response = $santi;
			break;
		case 'M':
		default:
			$response = $santo;	
			break;
	}

	$response = explode( "\n", $response );
	return wptexturize( $response[ mt_rand( 0, count( $response ) - 1 ) ] );
}

?>