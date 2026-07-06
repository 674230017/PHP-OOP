<?php
class Fruit {
  public $name;

  public function get_details() {
    echo "Name: " . $this->name . ".";
  }
}

$apple = new Fruit();
$apple->name = "Apple"; // Can be accessed directly
$apple->get_details();
?>