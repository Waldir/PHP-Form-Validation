<?php


Class Validate {

	private $fields = array();
	private $rules  = array();
	private $error;

	function __construct( $data, $rules )
	{
		$this->fields = $this->sanitize( $data );
		$this->rules  = $rules;
	}	

	/**
	* Finds errors by using the validation rules
	* and other variables.
	*/
	public function validate() 
	{
		try
		{
		// if there if no fields set an error;
		if( empty( $this->fields ) )
			throw new Exception( "There was no data to process" );

		// if the fields are not an array something is wrong
		if( !is_array( $this->fields ) )
			throw new Exception( "There was no data to process" );

		// Validate each form field
		foreach ( $this->fields as $field => $value )
		{
			// if the field is empty check if it should be.
			// if it shouldnt be throw an error.
			if( empty( $value ) )
				if( !empty( $this->rules[$field]['required'] ) )
					throw new Exception( $this->formattedError( $field, "cannot be blank" ) );

			// Else, if the field has a value and is declared in Rules
			if ( !empty( $this->rules[$field] ) ) 
			{
				unset( $this->rules[$field]['required'] );

			foreach( $this->rules[$field] as $rule => $ruleVal )
			{
				// find the right function to use within this instance
				$func_find = array( $this, $rule );

				// variables to pass into this function
				$func_vars = array( $field, $value, $ruleVal );

				// if a function with the same name as our rule is found run it
				// otherwise throw an error
				if( is_callable( $func_find ) )
					call_user_func_array( $func_find, $func_vars );
				else
					throw new Exception( "Invalid validation rule" );
			  }
			}
		}

		// catch any errors that were thrwon
		} catch ( Exception $e ) 
		{
			// set the error to be called.
			$this->error = $e->getMessage();
			return false;
		}

	// end of the line it all works.
	return true;

	} // end function

	/**
	 * Sanitize an array or string
	 * @param  array|string $data form submited data to be sanitized
	 * @return array|string $data data that has been sanitized
	 */
	private function sanitize( $data )
	{
		$data = preg_replace('/\s+/', ' ', $data);

		if( is_array( $data ) )
		{	
			$data = array_map( 'trim', $data );
			$data = filter_var_array( $data, FILTER_SANITIZE_STRING );
			return $data;
		
		} else { 
			$data = trim( $data );
			$data = filter_var( $data, FILTER_SANITIZE_STRING );
			return $data;
		}
	}

	/**
	 * Set the error in a dispaly friendly format.
	 * @param string $field the form field
	 * @param string $msg the desired messege to show for the error.
	 * @param string $this->error formated error.
	 * @return string 
	 */
	public function formattedError( $field = '', $msg )
	{
		$field = str_replace( "_", " ", $field );
		$field = ucfirst( $field );

		return empty( $field ) ? $field . ': ' . $msg : $msg;
	}

	/**
	 * Get the error messege set by setError()
	 * @param string $this->error error messege.
	 * @return string $this->error
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Get the form fiels that have been hopefuly sanitized
	 * @return array fields that orginaliy came form the form
	 */
	public function getFields()
	{
		return $this->fields;
	}


	/* ----------------------- Rules Functions -----------------------*/

	/**
	 * Throw Exception if field contains 
	 * anything other than alphabetical characters.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'alpha' => true ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function alpha( $field, $value ) 
	{
		if( !preg_match( '/^[A-Z.]+$/i', $value ) )
			throw new Exception( $this->formattedError( $field, "should contain only alphabetical characters" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * anything other than alphabetical characters and spaces.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'alpha_space' => true ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	public function alpha_space( $field, $value )
	{
		if( ctype_alpha( str_replace( ' ', '', $value ) ) === false )
		   throw new Exception( $this->formattedError( $field, "must contain letters and spaces only" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * anything other than numerical characters.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'numeric' => $number_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function numeric($field, $value)
	{
		if( !is_numeric( $value ) ) 
			throw new Exception( $this->formattedError( $field, "should be a numeric value" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * anything other than an interval.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'validate_int' => $int_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function validate_int( $field, $value )
	{
		if( !filter_var( $value, FILTER_VALIDATE_INT ) )
			throw new Exception( $this->formattedError( $field, "should be an integer value" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * anything other than a boolean value.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'validate_bool' => $bool_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function validate_bool( $field, $value )
	{
		if( !filter_var( $value, FILTER_VALIDATE_BOOLEAN ) )
			throw new Exception( $this->formattedError( $field, "should be a true or false value" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * anything other than a float value.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'validate_float' => $float_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function validate_float( $field, $value )
	{
		if( !filter_var( $value, FILTER_VALIDATE_FLOAT ) )
			throw new Exception( $this->formattedError( $field, "should be a float value" ) );
	}
	
	/**
	 * Throw Exception if field contains 
	 * anything other than a valid url.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'validate_url' => $url_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function validate_url( $field, $value )
	{
		if( !filter_var( $value, FILTER_VALIDATE_URL ) )
			throw new Exception( $this->formattedError( $field, "is not a valid url" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * less characters than the specified amount.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'min_length' => $number_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function min_length( $field, $value, $min_length )
	{
		if ( strlen( $value ) < $min_length )
		   throw new Exception( $this->formattedError( $field,  "should be {$min_length} characters or longer" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * more characters than the specified amount.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'max_length' => $number_value ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function max_length( $field, $value, $max_length )
	{
		if( strlen($value) > $max_length ) 
			throw new Exception( $this->formattedError( $field, "should be {$max_length} characters or shorter" ) );
	}

	/**
	 * Throw Exception if field contains 
	 * anything other than the values within the provided array.
	 *
	 * Usage: $rules = array( 'example_field' => array ( 'from_array' => $array ) );
	 *
	 * @param callable $callback
	 * @param string $field
	 * @param string $value
	 * @return Exception
	 */
	private function from_array( $field, $value, $array )
	{
		if( !in_array( $value, $array ) )
			throw new Exception( $this->formattedError( $field, "is not part of the allowed list" ) );
	}

} // end class
