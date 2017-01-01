<?php
 
// Set timezone
date_default_timezone_set('UTC');

class Partionner{

    private $_parent_table_name;
    private $_check_field;
    private $_initial_start_date; 
    private $_tables;
    private $_months_step;
    private $_create_child_tables = true;
    private $_create_indexes = true;
    private $_create_trigger_function = true;
    private $_create_trigger = true;
    private $_child_table_queries = "";
    private $_index_queries = "";
    private $_trigger_function_query = "";
    private $_trigger_query = "";
    private $_start_date;

	 public function __construct($parent_table_name, $check_field, $initial_start_date, $tables, $months_step){

	 	$this->_parent_table_name = $parent_table_name;
	 	$this->_check_field = $check_field;
	 	$this->_initial_start_date = $initial_start_date;
	 	$this->_tables = $tables;
	 	$this->_months_step = $months_step;
	 	$this->_start_date = $initial_start_date;
	 }

	 public function disable_Index(){
	 	$this->_create_indexes = false;
	 }

	 public function disable_Trigger(){
	 	$this->_create_trigger = false;
	 }

	 public function disable_Trigger_function(){
	 	$this->_create_trigger_function = false;
	 }

	 public function generate_child_table(){

	 	for ($i = 1; $i <= $this->_tables; $i++) {

	 		// Parse start date to array
			$start_date_array = date_parse($this->_start_date);
			// Add one month to end date
			$end_date = date("Y-m-d", mktime(0, 0, 0, $start_date_array["month"]+$this->_months_step, $start_date_array["day"], $start_date_array["year"]));
			// Generate new table name
			// Example table name projection_2010_1_6 means child table which starts at January 2010 and ends June 2010
			//$new_table_name = $this->_parent_table_name ."_". $start_date_array["year"] ."_". $start_date_array["month"] ."_". $this->_months_step;
			$new_table_name = $this->_parent_table_name ."_". $start_date_array["year"] ."_m_". $start_date_array["month"];

			// Child tables 
			if ($this->_create_child_tables) {
			$this->_child_table_queries .= "CREATE TABLE " . $new_table_name . " (" . "\n";
			$this->_child_table_queries .= "\t" . "CHECK ( " . $this->_check_field . " >= DATE '" . $this->_start_date . "' AND " . $this->_check_field . " < DATE '" . $end_date . "' )" . "\n";
			$this->_child_table_queries .= ") INHERITS (" . $this->_parent_table_name . ");" . "\n\n";
			}

			// Create Indexes
			if ($this->_create_indexes) {
			$this->_index_queries .= "CREATE INDEX " . $new_table_name . "_" . $this->_check_field . " ON " . $new_table_name . " (" . $this->_check_field . ");" . "\n";
			}

			// Create Trigger function (exact same rules as child tables)
			if ($this->_create_trigger_function) {
			// Function start and first if condition
			if ($this->_initial_start_date == $this->_start_date) {
			$this->_trigger_function_query .= "CREATE OR REPLACE FUNCTION " . $this->_parent_table_name . "_insert_trigger()" . "\n";
			$this->_trigger_function_query .= "RETURNS TRIGGER AS $$" . "\n";
			$this->_trigger_function_query .= "BEGIN" . "\n";
			$this->_trigger_function_query .= "\t" . "IF ( NEW." . $this->_check_field . " >= DATE '" . $this->_start_date . "' AND NEW." . $this->_check_field . " < DATE '" . $end_date . "' ) THEN " . "\n";
			$this->_trigger_function_query .= "\t\t" . "INSERT INTO " . $new_table_name . " VALUES (NEW.*); " . "\n";
			}
			else {
			$this->_trigger_function_query .= "\t" . "ELSIF ( NEW." . $this->_check_field . " >= DATE '" . $this->_start_date . "' AND NEW." . $this->_check_field . " < DATE '" . $end_date . "' ) THEN " . "\n";
			$this->_trigger_function_query .= "\t\t" . "INSERT INTO " . $new_table_name . " VALUES (NEW.*);" . "\n";
			}
			 
			// Else condition and Function end
			if ($i == $this->_tables) {
			$this->_trigger_function_query .= "\t" . "ELSE" . "\n";
			$this->_trigger_function_query .= "\t\t" . "RAISE EXCEPTION 'Date out of range.  Something wrong with the " . $this->_parent_table_name . "_insert_trigger() function!';" . "\n";
			$this->_trigger_function_query .= "\t" . "END IF;" . "\n";
			$this->_trigger_function_query .= "\t" . "RETURN NULL;" . "\n";
			$this->_trigger_function_query .= "END;" . "\n";
			$this->_trigger_function_query .= "$$" . "\n";
			$this->_trigger_function_query .= "LANGUAGE plpgsql;" . "\n";
			}
			}
			 
			// Next start date is last end date
			$this->_start_date = $end_date;
			}
            
            // Create Trigger to parent table
			if ($this->_create_trigger) {
			$this->_trigger_query .= "CREATE TRIGGER insert_" . $this->_parent_table_name . "_trigger " . "\n";
			$this->_trigger_query .= "\t" . "BEFORE INSERT ON " . $this->_parent_table_name . " " . "\n";
			$this->_trigger_query .= "\t" . "FOR EACH ROW EXECUTE PROCEDURE " . $this->_parent_table_name . "_insert_trigger();" . "\n";
			}
	 
	 		// Print all selected
			if ($this->_create_child_tables) {
			echo $this->_child_table_queries;
			echo "\n" . "-----------------------------------------------" . "\n\n";
			}
			if ($this->_create_indexes) {
			echo $this->_index_queries;
			echo "\n" . "-----------------------------------------------" . "\n\n";
			}
			if ($this->_create_trigger_function) {
			echo $this->_trigger_function_query;
			echo "\n" . "-----------------------------------------------" . "\n\n";
			}
			if ($this->_create_trigger) {
			echo $this->_trigger_query;
			}

        }
}
