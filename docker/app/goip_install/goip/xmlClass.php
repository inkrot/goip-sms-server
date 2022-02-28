<?php

/* 
(c) 2000 Hans Anderson Corporation. All Rights Reserved. 
You are free to use and modify this class under the same 
guidelines found in the PHP License. 
----------- 
bugs/me: 
http://www.hansanderson.com/php/ 
me@hansanderson.com 
----------- 
Version 1.0 
- 1.0 is the first actual release of the class. It's 
finally what I was hoping it would be, though there 
are likely to still be some bugs in it. This is 
a much changed version, and if you have downloaded 
a previous version, this WON'T work with your existing 
scripts! You'll need to make some SIMPLE changes. 
- .92 fixed bug that didn't include tag attributes 
(to use attributes, add _attributes[array_index] 
to the end of the tag in question: 
$xml_html_head_body_img would become 
$xml_html_head_body_img_attributes[0], 
for example) 
-- Thanks to Nick Winfield <nick@wirestation.co.uk> 
for reporting this bug. 
- .91 No Longer requires PHP4! 
- .91 now all elements are array. Using objects has 
been discontinued. 
----------- 
What class.xml.php is: 
A very, very easy to use XML parser class. It uses PHP's XML functions 
for you, returning one array that has all the tag information. The only 
hard part is figuring out the syntax of the tags! 
----------- 
Sample use:
require('class.xml.php'); 
$file = "data.xml"; 
$data = implode("",file($file)) or die("could not open XML input file"); 
$obj = new xml($data,"xml"); 
print $xml["hans"][0]->num_results[0]; 
for($i=0;$i<sizeof($xml["hans"]);$i++) { 
print $xml["hans"][$i]->tag[0] . "\n\n"; 
} 
To print url attributes (if they exist): 
print $xml["hans"][0]->attributes[0]["size"]; # where "size" was an attr name

(that's it! slick, huh?) 
----------- 
Two ways to call xml class: 
$xml = new xml($data); 
- or - 
$xml = new xml($data,"jellyfish"); 
The second argument (jellyfish) is optional. Default is 'xml'. 
All the second argument does is give you a chance to name the array 
that is returned something besides "xml" (in case you are already using 
that name). Normal PHP variable name rules apply. 
---------- 
Explanation of xml class: 
This class takes valid XML data as an argument and 
returns all the information in a complex but loopable array. 
Here's how it works: 
Data: 
<html> 
<head> 
<title>Hans Anderson's XML Class</title> 
</head> 
<body> 
</body> 
</html> 
Run the data through my class, then access the title like this: 
$xml["html_head"][0]->title[0]; 
Or, loop through them: 
for($i=0;$i<sizeof($xml["html_head"]);$i++) { 
print $xml["html_head"][$i]->title[0] . "\n"; 
} 
Yes, the variable names *are* long and messy, but it's 
the best way to create the tree, IMO. 
Here is a complex explanation I sent to one class.xml.php user: 
--------- 
> Now I've run into another problem: 
> 
> <STORY TIMESTAMP="2000-12-15T20:08:00,0"> 
> <SECTION>Markets</SECTION> 
> <BYLINE>By <BYLINE_AUTHOR ID="378">Aaron L. Task</BYLINE_AUTHOR><BR/>Senior 
> Writer</BYLINE> 
> </STORY> 
> 
> How do I get BYLINE_AUTHOR? 
print $xml["STORY_BYLINE"][0]->BYLINE_AUTHOR[0]; 
> And just a little question: Is there an easy way to get TIMESTAMP? 
print $xml["STORY"][0]->attributes[0]["TIMESTAMP"]; 
This is confusing, I know, but it's the only way I could really do 
this. Here's the rundown: 
The $xml part is an array -- an array of arrays. The first array is the 
name of the tag -- in the first case above, this is the tag STORY, and 
below that BYLINE. You want BYLINE_AUTHOR. You want the first BA. The 
first one is index [0] in the second part of the two-dimensional array. 
Even if there is only *one* byline author, it's still an array, and you 
still have to use the [0]. Now, the two-dimensional array is storing 
dynamic structures -- objects in this case. So, we need to dereference 
the object, hence the ->. The BYLINE_AUTHOR is the tag you want, and it 
is an array in that object. The reason for the array is that if there are 
more than one BYLINE_AUTHOR for the tags STORY, BYLINE, we would have a 
[0] and [1] in the array. In your case there is just the one. 
*** This is very confusing, I know, but once you understand it, the power 
of this method will be more apparent. You have access to *every* bit of 
information in the XML file, without having to do anything but understand 
how to refer to the variables. *** 
EVERY variable will look like this: 
print $xml["STORY_BYLINE"][0]->BYLINE_AUTHOR[0]; 
The trick is understanding how to get the variable to give you the 
information. This is an array of arrays of objects holding arrays! 
Any tag that has attributes will have them stored in a special object 
array named "attributes" and will be called this way: 
print $xml["STORY"][0]->attributes[0]["TIMESTAMP"]; 
If you aren't sure if there are attributes, you could do isset() or 
is_array() for that above example. If isset(), you could for loop and 
while(list($k,$v) = each($xml...)) over it to get the values. 
array of 
objects 
| 
| 
$xml["STORY_BYLINE"][0]->BYLINE_AUTHOR[0]; 
^ ^ 
array of ^ 
arrays ^ 
^ 
array in 
object 
In general, to get the value of this: 
<STATE> 
<STATENAME></STATENAME> 
<COUNTY> 
<COUNTYNAME></COUNTYNAME> 
<CITY></CITY> 
<CITY></CITY> 
</COUNTY> 
<COUNTY> 
<COUNTYNAME></COUNTYNAME> 
<CITY></CITY> 
<CITY></CITY> 
</COUNTY> 
</STATE> 
You would look for what you want, say "CITY", then go UP one level, to 
COUNTY (COUNTYNAME is on the same 'level'), for your first array: 
$xml["STATE_COUNTY"] -- ALL tags pushed together are separated with 
"_". Otherwise tags are as they were -- spaces, dashes, CaSe, etc. 
Now, you want the first COUNTY, though there are two, so we are do this: 
$xml["STATE_COUNTY"][0] -- to get the second, we'd use [1] instead of 
[0]. You could also do a for() loop through it, using sizeof() to figure 
out how big it is. 
So, we have the STATE,COUNTY we want -- the first one. It's an 
object, and we know we want the CITY. So, we dereference the object. The 
name of the array we want is, of course, CITY: 
$xml["STATE_COUNTY"][0]->CITY[0] (the first one, the second one would be 
[1]). 
And that's it. Basically, find what you want, and go up a level. 
You could do some complex for loops to go through them all, too: 
for($i=0;$i<sizeof($xml["STATE_COUNTY"]);$i++) { 
for($j=0;$j<sizeof($xml["STATE_COUNTY"][0]->CITY);$j++) { 
print $xml["STATE_COUNTY"][$i]->CITY[$j]; 
} 
} 
----------- 
Whew. I hope that helps, not hurts. 
*/ 
/* used to store the parsed information */ 
class xml_container { 
function store($k,$v) { 
   $this->{$k}[] = $v; 
}
} 
/* parses the information */ 
class xml { 
// initialize some variables 
var $current_tag=array(); 
var $xml_parser; 
var $Version = 1.0; 
var $tagtracker = array(); 
/* Here are the XML functions needed by expat */ 
/* when expat hits an opening tag, it fires up this function */ 
function startElement($parser, $name, $attrs) { 
   array_push($this->current_tag, $name); // add tag to the cur. tag array 
   $curtag = implode("_",$this->current_tag); // piece together tag 
   /* this tracks what array index we are on for this tag */ 
   if(isset($this->tagtracker["$curtag"])) { 
    $this->tagtracker["$curtag"]++; 
   } else { 
    $this->tagtracker["$curtag"]=0; 
   } 
   /* if there are attributes for this tag, we set them here. */ 
   if(count($attrs)>0) { 
    $j = $this->tagtracker["$curtag"]; 
    if(!$j) $j = 0; 
    if(!is_object($GLOBALS[$this->identifier]["$curtag"][$j])) { 
     $GLOBALS[$this->identifier]["$curtag"][$j] = new xml_container; 
    } 
    $GLOBALS[$this->identifier]["$curtag"][$j]->store("attributes",$attrs); 
   } 
} // end function startElement 
/* when expat hits a closing tag, it fires up this function */ 
function endElement($parser, $name) { 
   $curtag = implode("_",$this->current_tag); // piece together tag 
   // before we pop it off, 
   // so we can get the correct 
   // cdata 
   if(!$this->tagdata["$curtag"]) { 
    $popped = array_pop($this->current_tag); // or else we screw up where we are 
    return; // if we have no data for the tag 
   } else { 
    $TD = $this->tagdata["$curtag"]; 
    unset($this->tagdata["$curtag"]); 
   } 
   $popped = array_pop($this->current_tag); 
   // we want the tag name for 
   // the tag above this, it 
   // allows us to group the 
   // tags together in a more 
   // intuitive way. 
   if(sizeof($this->current_tag) == 0) return; // if we aren't in a tag 
   $curtag = implode("_",$this->current_tag); // piece together tag 
   // this time for the arrays 
   $j = $this->tagtracker["$curtag"]; 
   if(!$j) $j = 0; 
   if(!is_object($GLOBALS[$this->identifier]["$curtag"][$j])) { 
    $GLOBALS[$this->identifier]["$curtag"][$j] = new xml_container; 
   } 
   $GLOBALS[$this->identifier]["$curtag"][$j]->store($name,$TD); #$this->tagdata["$curtag"]); 
   unset($TD); 
   return TRUE; 
} 
/* when expat finds some internal tag character data, 
it fires up this function */ 
function characterData($parser, $cdata) { 
   $curtag = implode("_",$this->current_tag); // piece together tag 
   $this->tagdata["$curtag"] .= $cdata; 
} 
/* this is the constructor: automatically called when the class is initialized */ 
function xml($data,$identifier='xml') { 
   $this->identifier = $identifier; 
   // create parser object 
   $this->xml_parser = xml_parser_create(); 
   // set up some options and handlers 
   xml_set_object($this->xml_parser,$this); 
   xml_parser_set_option($this->xml_parser,XML_OPTION_CASE_FOLDING,0); 
   xml_set_element_handler($this->xml_parser, "startElement", "endElement"); 
   xml_set_character_data_handler($this->xml_parser, "characterData"); 
  
   if (!xml_parse($this->xml_parser, $data, TRUE)) { 
    sprintf("XML error: %s at line %d", 
    xml_error_string(xml_get_error_code($this->xml_parser)), 
    xml_get_current_line_number($this->xml_parser)); 
   } 
   // we are done with the parser, so let's free it 
   xml_parser_free($this->xml_parser); 
} // end constructor: function xml() 
} // thus, we end our class xml 
?>