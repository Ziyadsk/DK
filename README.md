<img src="DK_img.png" height="100">

#### Another way to write your php

## Installation 

### Via Composer 
    composer global require ziyadsk/dk:dev-master
#### Add the composer bin to your path
    echo 'export PATH="$PATH:$HOME/.composer/vendor/bin"' >> ~/.bashrc

## Usage 
```bash
dk translate [file] [destination]
```
#### - If no destination is given the current directory will be the destination.

## Features
### **DK** transform more readable code into php 
#### Removed semicolons and dollar signs and openning tags :
```php
    name = "John Doe"
    echo("Hello")
```
to 

```php
<?php
    $name = "John Doe" ; 
    echo($name);
?>
```
#### Shorter syntax for functions and loops :
```rust
fn print_100_times(variable){
    for(i in {1..100}){
        print(variable)
    }
}
```
to 

```php
<?php
    function print_100_times($variable){
        for($i=0 ;$i<=100,$i++){
            print($variable);
        }
    }
?>
```
#### More readable foreach loops :
```rust
    for(element in my_arr){
        print(elem)
    }
}
```
to 

```php
<?php
    foreach($my_arr as $element){
        print($element);
    }
?>
```
#### classes , class propreties and interfaces:

```java
    class Car : Vehicule [SomeInterface,Runnable] {
        serial_number = "9819020Z0DJLOZEOLD"
        pub name = "Honda"
        pub fn start_engine(){}
      
    }
    myCar = new Car()
    myCar->start_engine() 
```
to 

```php
<?php
    class Car extends Vehicule implements SomeInterface, AnotherInterFace {
        static $name = "Honda";
        private $serial_number = "9819020Z0DJLOZEOLD";
        public function start_engine() {}
    }
    $myCar = new Car() ; 
    $myCar->start_engine() ; 
?>
```
## Editor Support
- VScode extension -> [DK](https://marketplace.visualstudio.com/items?itemName=ziyadsk.DK)

## License
[MIT]()