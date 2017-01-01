 <?php 
class PartitionnerTestUnit extends PHPUnit_Framework_TestCase
{ 
	private $chiltable;
 
	public function setUp()
	{
		$this->chiltable = new Partionner("projection","start_datetime","2017-01-01",4,1);
		$this->assertNull(error_get_last());
	}
	public function testGenerate()
    {
		$this->chiltable->generate_child_table();
		$this->assertNull(error_get_last());
	}

}
 ?>