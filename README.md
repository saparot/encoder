# saparot encoder!


## Description

saparot encoder **helps** you to **convert strings and arrays** between various encodings. 
Main intention of that set small lib was to make it possible to **upgrade a latin-1 based running site into UTF-8**.


## License

Copyright (c) **2019** **saparot.developmnet@gmail.com** 

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE..


## Converting 

This library does not convert itself, but handles exceptions between various LATIN-1 encodings like ISO-8859-1, WINDOWS-1252, ISO-8859-15. Convert is done by **mb-functions**


## Features
- Detection to find best matching encoding for a string
- Debug mode
- Deep Convert of Arrays, including keys (optional)

## Requirements
- php >= 7
- ext-mbstring

## Installation via Composer
`composer require saparot/encoder`

> get composer: https://getcomposer.org/ 
 
## Basics

### detect encoding of a string 
```
$languageGroup = Encoder\Encoder::ENC_GROUP_LATIN;
try {
	$e = new Encoder\Encoder();
	$detectedEncoding = $e->detect($yourString);
} catch (Encoder\Exception $e) {
	//ups.
}
```

### convert a string to UTF-8
```
$languageGroup = Encoder\Encoder::ENC_GROUP_LATIN;
try {
	$e = new Encoder\Encoder();
	$convertedString = $e->convertString($yourString);
} catch (Encoder\Exception $e) {
	//ups.
}
```

### convert a array to UTF-8
```
$languageGroup = Encoder\Encoder::ENC_GROUP_LATIN;
try {
	$e = new Encoder\Encoder();
	$convertedArray = $e->convertArray($yourString);
} catch (Encoder\Exception $e) {
	//ups.
}
```