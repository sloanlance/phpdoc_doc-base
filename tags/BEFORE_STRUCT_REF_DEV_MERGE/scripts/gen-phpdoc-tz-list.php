<?php
	$groupedList = array();
	$aliasList = array(
		'Brazil' => 'Others',
		'Canada' => 'Others',
		'Chile' => 'Others',
		'Etc' => 'Others',
		'Mexico' => 'Others',
		'US' => 'Others',
	);
	$list = timezone_identifiers_list();

	foreach ($list as $element) {
		if (preg_match('@^([^/]*)/(.*)@', $element, $m)) {
			$group = $m[1];
		} else {
			$group = 'Others';
		}
		if (isset($aliasList[$group])) {
			$group = $aliasList[$group];
		}
		$groupedList[$group][] = $element;
	}
    ksort($groupedList);
    $others = $groupedList['Others'];
    unset($groupedList['Others']);
    $groupedList['Others'] = $others;
?>
<?php echo '<?'; ?>xml version="1.0" encoding="iso-8859-1"?>
<!-- $Revision$ -->
<!-- AUTO GENERATED, DO NOT MODIFY BY HAND -->

<appendix id="timezones">
 <title>List of Supported Timezones</title>
 <para>
  Here you'll find the complete list of timezones supported by PHP, which are
  meant to be used with e.g. <function>date_default_timezone_set</function>.
 </para>
 <note>
  <simpara>
   The latest version of the timezone database can be installed via PECL's
   <ulink url="&url.pecl.package.get;timezonedb">timezonedb</ulink>.
   For Windows users, a pre-compiled DLL can be downloaded from the PECL4Win
   site: <ulink url="&url.pecl.timezonedb.dll;">php_timezonedb.dll</ulink>.
  </simpara>
 </note>
<?php foreach ($groupedList as $group => $zones) { ?>

 <sect1 id="timezones.<?php echo strtolower($group); ?>">
  <title>List of timezones in the group <?php echo $group; ?></title>
  <table>
   <title><?php echo $group; ?></title>
   <tgroup cols="5">
    <tbody>
<?php
    $c = 0;
    foreach($zones as $zone) {
        if ($c % 5 == 0) {
            echo "     <row>\n";
        }
        $c++;
        echo "      <entry>{$zone}</entry>\n";
        if ($c % 5 == 0) {
            echo "     </row>\n";
        }
    }
    if ($c % 5 != 0) {
        echo "     </row>\n";
    }
?>
    </tbody>
   </tgroup>
  </table>
<?php if ( $group == 'Others' ) { ?>
  <note>
   <simpara>
     Please do not use any of the timezones listed here, they only exist
     for backward compatible reasons.
   </simpara>
  </note>
<?php } ?>
 </sect1>
<?php } ?>
</appendix>

<!-- Keep this comment at the end of the file
Local variables:
mode: sgml
sgml-omittag:t
sgml-shorttag:t
sgml-minimize-attributes:nil
sgml-always-quote-attributes:t
sgml-indent-step:1
sgml-indent-data:t
indent-tabs-mode:nil
sgml-parent-document:nil
sgml-default-dtd-file:"../../manual.ced"
sgml-exposed-tags:nil
sgml-local-catalogs:nil
sgml-local-ecat-files:nil
End:
vim600: syn=xml fen fdm=syntax fdl=2 si
vim: et tw=78 syn=php
vi: ts=4 sw=1
-->
