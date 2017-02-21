<?php

if(isset($_POST)) {

	$home_currency = $_POST['home_currency'];
	$convert_to = $_POST['convert_to'];
	$output_type = $_POST['output_type'];

	$rate_object = CurrencyExchange::get_exchange_rates($home_currency, $convert_to, $output_type);
	
	die(json_encode($rate_object));
}

/**
 * Currency Exchange class queries the yahoo finance api for current exchange rates, opens the file and reads the result for onward storage to the database.
 */
class CurrencyExchange {
	
	public static function get_exchange_rates( $home_currency, $convert_to, $output_type ) {
		/*
		* @default_targets: A list of default currencies
		*/
		$default_targets = array( 
			'NGN'=>'Nigerian Naira',
			'USD'=>'US Dollar',
			'EUR'=>'Euro',
			'GBP'=>'British Pound',
			'GHS'=>'Ghanian Cedi',
			'CNY'=>'Chinese Yuan',
			'KES'=>'Kenyan Shilling'
		);
		
		$target_currencies = array();
		$target_currencies = $default_targets;
		
		/*
		* @unset: Remove home currency from targets
		*/
		if( array_key_exists( $home_currency, $target_currencies ) ) {
			unset( $target_currencies[$home_currency] );
		}
		
		/*
		* @loop: Loop through the targets and perform lookup on Yahoo! Finance
		*/
		foreach( $target_currencies as $code => $name ) {
			/*
			* @url: Get the URL for csv file at Yahoo API, based on 'convert_to' option
			*/
			switch( strtoupper( $convert_to ) ) {
				case 'H': /* Converts target to home */
					$url = sprintf( "http://finance.yahoo.com/d/quotes.csv?s=%s%s=X&f=sl1d1t1", $code, $home_currency );
				break;
				case 'T': /* Converts home to target */
				default:
					$url = sprintf( "http://finance.yahoo.com/d/quotes.csv?s=%s%s=X&f=sl1d1t1", $home_currency, $code );
				break;
			}
			
			/*
			* @fopen: open and read API files
			*/
			$handle = @fopen( $url, 'r' );
			if ( $handle ) {
				$result = fgets( $handle, 4096 );
				fclose( $handle );
			}
			
			/*
			* @output: Create output array and add currency code and descriptive (Country) name
			*/
			$arrOutput[$code] = explode( ',', $result );
			array_unshift( $arrOutput[$code], $code ); /* Add the code */
			array_unshift( $arrOutput[$code], $name ); /* Add the name */
			
			/*
			* @keys: Substitute numerical keys with user friendly ones
			*/
			$arrOutput[$code] = self::add_cx_keys( $arrOutput[$code] );
		}
		
		/*
		* @object: Convert array to object if required
		*/
		if( strtoupper( $output_type ) == 'OBJECT' ) {
			$arrOutput = self::make_array_object( $arrOutput );
		}
		
		/*
		* @return: Return the output array or object
		*/
		return $arrOutput;
	}

	/*
	* @function: add_cx_keys( $array )
	*/
	static function add_cx_keys( $array ) {
		/*
		* @keys: Define the desired array keys in an array
		*/
		$target_keys = array( 'cx_name', 'cx_code', 'cx_test', 'cx_rate', 'cx_date', 'cx_time' );
		$i = 0;
		foreach($target_keys as $key) {
			$arrOutput[$key] = $array[$i];
			$i++;
		}
		return $arrOutput;
	}

	/*
	* @function: make_array_object() | array_to_object() - convert an associative array to an object
	*/
	static function make_array_object( $array ) {
		$object = new stdClass();
		return self::array_to_object( $array, $object );
	}

	static function array_to_object( $arr, $obj ) {
		foreach( $arr as $key=>$val )
		{
			if( is_array( $val ) )
			{
				$obj->$key = new stdClass();
				self::array_to_object( $val, $obj->$key );
			}
			else
			{
				$obj->$key = $val;
			}
		}
		return $obj;
	}
}

?>