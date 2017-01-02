 <?php 
class PartitionnerTestUnit extends PHPUnit_Framework_TestCase
{ 
	 private $chiltable;
 
	 public function setUp()
	{
		$this->chiltable = new Partionner("projection","start_datetime","2017-01-01",4,1);
		$this->assertTrue(isset($this->chiltable));
	}
	public function testGenerate()
    {
		$this->chiltable->generate_child_table();
		$this->assertTrue(isset($this->chiltable));
	}
}
 ?>
