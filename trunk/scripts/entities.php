#!/usr/bin/php -q
<?php

if ($argc > 3 || in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

Find entity usage in phpdoc xml files and
list used and unused entities.

  Usage:
  <?=$argv[0]?> [<entity-file>] [<language-code>]

  <entity-file> must be a file name (with relative
  path from the phpdoc root) to a file containing
  <!ENTITY...> definitions. Defaults to global.ent.

  <language-code> must be a valid language code used in the repository, or
  'all' for all languages. Defaults to en.

  The script will generate an entity_usage.txt
  file, containing the entities defined in the
  <entity-file>.
  
  Written by Gabor Hojtsy <goba@php.net>, 2001-09-22

<?php
  exit;
}

// CONFIG SECTION
$docdir = "../"; // Main directory of the PHP documentation (one dir up in cvs)

/*********************************************************************/
/* Nothing to modify below this line                                 */
/*********************************************************************/

// Long runtime
set_time_limit(0);

// Array to collect the entities
$defined_entities = array();

// Default values
$langcode = "en";
$filename = "global.ent";

// Parameter value copying
if ($argc == 3) { 
    $langcode = $argv[2];
	if ($langcode === 'all') {
		$langcode = '..';
	}
}

if ($argc >= 2) {
    $filename = $argv[1];
}
  
/*********************************************************************/
/* Here starts the functions part                                    */
/*********************************************************************/

// Extract the entity names from the file
function extract_entity_definitions ($filename, &$entities)
{
    // Read in the file, or die
    $file_array = file ($filename);
    if (!$file_array) { die ("Cannot open entity file ($filename)."); }
    
    // The whole file in a string
    $file_string = preg_replace("/[\r\n]/", "", join ("", $file_array));
    
    // Find entity names
    preg_match_all("/<!ENTITY\s+(.*)\s+/U", $file_string, $entities_found);
    $entities_found = $entities_found[1];
    
    // Convert to hash
    foreach ($entities_found as $entity_name) {
      $entities[$entity_name] = array();
    }

    // Return with a useful regexp part
    return "&(" . join("|", $entities_found) . ");";
    
} // extract_entity_definitions() function end

// Checks a diretory of phpdoc XML files
function check_dir($dir, &$defined_entities, $entity_regexp)
{
    // Collect files and diretcories in these arrays
    $directories = array();
    $files = array();
    
    // Open and traverse the directory
    $handle = @opendir($dir);
    while ($file = @readdir($handle)) {
      if (preg_match("/^\.{1,2}/",$file) || $file == 'CVS')
        continue;

      // Collect files and directories
      if (is_dir($dir.$file)) { $directories[] = $file; }
      else { $files[] = $file; }

    }
    @closedir($handle);
      
    // Sort files and directories
    sort($directories);
    sort($files);
      
    // Files first...
    foreach ($files as $file) {
      check_file($dir.$file, $defined_entities, $entity_regexp);
    }

    // than the subdirs
    foreach ($directories as $file) {
      check_dir($dir.$file."/", $defined_entities, $entity_regexp);
    }
} // check_dir() function end

function check_file ($filename, &$defined_entities, $entity_regexp)
{
    // Read in file contents
    $contents = preg_replace("/[\r\n]/", "", join("", file($filename)));
    
    // Find all entity usage in this file
    preg_match_all("/$entity_regexp/U", $contents, $entities_found);
    
    // No entities found
    if (count($entities_found[1]) == 0) { return; }
    
    // New occurances found, so increase the number
    foreach ($entities_found[1] as $entity_name) {
        if (isset($defined_entities[$entity_name])) { $defined_entities[$entity_name][] = $filename; }
    }

} // check_file() function end
  
/*********************************************************************/
/* Here starts the program                                           */
/*********************************************************************/

// Check for directory validity
if (!@is_dir($docdir . $lang)) {
  die("The $lang language code is not valid");
}  
  
// If directory is OK, start with the header
echo "\nStart searching ...\n";

$entity_regexp = extract_entity_definitions($docdir . $filename, $defined_entities);

// Check the requested directory
check_dir("$docdir$langcode/", $defined_entities, $entity_regexp);

echo "Generating entity_usage.txt ...\n";

$fp = fopen("entity_usage.txt", "w");
fwrite($fp, "ENTITY USAGE STATISCTICS

=========================================================
In this file you can find entity usage stats compiled
from the entity file: $filename. The entity usage
was tested in the $langcode tree at phpdoc. You may
find many unused entities here. Please do not delete
the entities, unless you make sure, no translation
makes use of the entity. Interestingly, the purpouse
of this statistics is to reduce the number of unused
entities in phpdoc. Here comes the numbers and file
names:
=========================================================

");

foreach ($defined_entities as $entity_name => $files) {
    $num = count($files);
    if ($num == 0) { $prep = "++ "; } else { $prep = "   "; }
    fwrite($fp, $prep . sprintf("%-30s %d", $entity_name, count($files)). "\n");
}

fclose($fp);

echo "Done!\n";

?>
