<?php
class Fruit {
  private $name;

  public function get_details() {
    echo "Name: " . $this->name . ".";
  }
}

$apple = new Fruit();
$apple->name = "Apple"; // Error: Cannot access private property
$apple->get_details();
?>
