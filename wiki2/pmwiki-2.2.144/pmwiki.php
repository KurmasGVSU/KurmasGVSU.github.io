<?php
/*
    PmWiki
    Copyright 2001-2021 Patrick R. Michaud
    pmichaud@pobox.com
    http://www.pmichaud.com/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

    ----
    Note from Pm:  Trying to understand the PmWiki code?  Wish it had 
    more comments?  If you want help with any of the code here,
    write me at <pmichaud@pobox.com> with your question(s) and I'll
    provide explanations (and add comments) that answer them.
    
    Script maintained by Petko YOTOV www.pmwiki.org/petko
    $Id: pmwiki.php 3752 2021-10-24 13:09:34Z petko $
*/
error_reporting(E_ALL ^ E_NOTICE);
StopWatch('PmWiki');
@ini_set('magic_quotes_runtime', 0);
@ini_set('magic_quotes_sybase', 0);
if (@ini_get('pcre.backtrack_limit') < 1000000) 
  @ini_set('pcre.backtrack_limit', 1000000);
if (ini_get('register_globals')) 
  foreach($_REQUEST as $k=>$v) { 
    if (preg_match('/^(GLOBALS|_SERVER|_GET|_POST|_COOKIE|_FILES|_ENV|_REQUEST|_SESSION|FarmD|WikiDir)$/i', $k)) exit();
    ${$k}=''; unset(${$k}); 
  }
$UnsafeGlobals = array_keys($GLOBALS); $GCount=0; $FmtV=array();
$FmtV['$TokenName'] = 'pmtoken';
SDV($FarmD,dirname(__FILE__));
SDV($WorkDir,'wiki.d');
define('PmWiki',1);
if (preg_match('/\\w\\w:/', $FarmD)) exit();
@include_once("$FarmD/scripts/version.php");
$GroupPattern = '[[:upper:]][\\w]*(?:-\\w+)*';
$NamePattern = '[[:upper:]\\d][\\w]*(?:-\\w+)*';
$BlockPattern = 'form|div|table|t[rdh]|p|[uo]l|d[ltd]|h[1-6r]|pre|blockquote';
$WikiWordPattern = '[[:upper:]][[:alnum:]]*(?:[[:upper:]][[:lower:]0-9]|[[:lower:]0-9][[:upper:]])[[:alnum:]]*';
$WikiDir = new PageStore('wiki.d/{$FullName}');
$WikiLibDirs = array(&$WikiDir,new PageStore('$FarmD/wikilib.d/{$FullName}'));
$PageFileEncodeFunction = 'PUE'; # only used if $WikiDir->encodefilenames is set
$PageFileDecodeFunction = 'urldecode';
$LocalDir = 'local';
$InterMapFiles = array("$FarmD/scripts/intermap.txt",
  "$FarmD/local/farmmap.txt", '$SiteGroup.InterMap', 'local/localmap.txt');
$Newline = "\263";                                 # deprecated, 2.0.0
$KeepToken = "\034\034";
$Now=time();
define('READPAGE_CURRENT', $Now+604800);
$TimeFmt = '%B %d, %Y, at %I:%M %p';
$TimeISOFmt = '%Y-%m-%dT%H:%M:%S';
$TimeISOZFmt = '%Y-%m-%dT%H:%M:%SZ';
$MessagesFmt = array();
$BlockMessageFmt = "<h3 class='wikimessage'>$[This post has been blocked by the administrator]</h3>";
$EditFields = array('text');
$EditFunctions = array('AutoCheckToken', 'EditTemplate', 'RestorePage', 'ReplaceOnSave',
  'SaveAttributes', 'PostPage', 'PostRecentChanges', 'AutoCreateTargets',
  'PreviewPage');
$EnablePost = 1;
$ChangeSummary = substr(preg_replace('/[\\x00-\\x1f]|=\\]/', '', 
        stripmagic(@$_REQUEST['csum'])), 0, 100);
$AsSpacedFunction = 'AsSpaced';
$SpaceWikiWords = 0;
$RCDelimPattern = '  ';
$RecentChangesFmt = array(
  '$SiteGroup.AllRecentChanges' => 
    '* [[{$Group}.{$Name}]]  . . . $CurrentTime $[by] $AuthorLink: [=$ChangeSummary=]',
  '$Group.RecentChanges' =>
    '* [[{$Group}/{$Name}]]  . . . $CurrentTime $[by] $AuthorLink: [=$ChangeSummary=]');
$UrlScheme = (@$_SERVER['HTTPS']=='on' || @$_SERVER['SERVER_PORT']==443)
             ? 'https' : 'http';
$ScriptUrl = $UrlScheme.'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$PubDirUrl = preg_replace('#/[^/]*$#', '/pub', $ScriptUrl, 1);
$HTMLVSpace = "<vspace>";
$HTMLPNewline = '';
$MarkupFrame = array();
$MarkupFrameBase = array('cs' => array(), 'vs' => '', 'ref' => 0,
  'closeall' => array(), 'is' => array(),
  'escape' => 1);
$WikiWordCountMax = 1000000;
$WikiWordCount['PmWiki'] = 1;
$TableRowIndexMax = 1;
$UrlExcludeChars = '<>"{}|\\\\^`()[\\]\'';
$QueryFragPattern = "[?#][^\\s$UrlExcludeChars]*";
$SuffixPattern = '(?:-?[[:alnum:]]+)*';
$LinkPageSelfFmt = "<a class='selflink' href='\$LinkUrl' title='\$LinkAlt'>\$LinkText</a>";
$LinkPageExistsFmt = "<a class='wikilink' href='\$LinkUrl' title='\$LinkAlt'>\$LinkText</a>";
$LinkPageCreateFmt = 
  "<a class='createlinktext' rel='nofollow' title='\$LinkAlt'
    href='{\$PageUrl}?action=edit'>\$LinkText</a><a rel='nofollow' 
    class='createlink' href='{\$PageUrl}?action=edit'>?</a>";
$UrlLinkFmt = 
  "<a class='urllink' href='\$LinkUrl' title='\$LinkAlt' rel='nofollow'>\$LinkText</a>";
umask(002);
$CookiePrefix = '';
$SiteGroup = 'Site';
$SiteAdminGroup = 'SiteAdmin';
$DefaultGroup = 'Main';
$DefaultName = 'HomePage';
$GroupHeaderFmt = '(:include {$Group}.GroupHeader self=0 basepage={*$FullName}:)(:nl:)';
$GroupFooterFmt = '(:nl:)(:include {$Group}.GroupFooter self=0 basepage={*$FullName}:)';
$PagePathFmt = array('{$Group}.$1','$1.$1','$1.{$DefaultName}');
$PageAttributes = array(
  'passwdread' => '$[Set new read password:]',
  'passwdedit' => '$[Set new edit password:]',
  'passwdattr' => '$[Set new attribute password:]');
$XLLangs = array('en');
if (preg_match('/^C$|\.UTF-?8/i',setlocale(LC_ALL,0)))
  setlocale(LC_ALL,'en_US');
$FmtP = array();
$FmtPV = array(
  # '$ScriptUrl'    => 'PUE($ScriptUrl)',   ## $ScriptUrl is special
  '$PageUrl'      => 
    'PUE(($EnablePathInfo) 
         ? "$ScriptUrl/$group/$name"
         : "$ScriptUrl?n=$group.$name")',
  '$FullName'     => '"$group.$name"',
  '$Groupspaced'  => '$AsSpacedFunction($group)',
  '$Namespaced'   => '$AsSpacedFunction($name)',
  '$Group'        => '$group',
  '$Name'         => '$name',
  '$Titlespaced'  => 'FmtPageTitle(@$page["title"], $name, 1)',
  '$Title'        => 'FmtPageTitle(@$page["title"], $name, 0)',
  '$LastModifiedBy' => '@$page["author"]',
  '$LastModifiedHost' => '@$page["host"]',
  '$LastModified' => 'strftime($GLOBALS["TimeFmt"], $page["time"])',
  '$LastModifiedSummary' => '@$page["csum"]',
  '$LastModifiedTime' => '$page["time"]',
  '$Description'  => '@$page["description"]',
  '$SiteGroup'    => '$GLOBALS["SiteGroup"]',
  '$SiteAdminGroup' => '$GLOBALS["SiteAdminGroup"]',
  '$VersionNum'   => '$GLOBALS["VersionNum"]',
  '$Version'      => '$GLOBALS["Version"]',
  '$WikiTitle'    => '$GLOBALS["WikiTitle"]',
  '$Author'       => 'NoCache($GLOBALS["Author"])',
  '$AuthId'       => 'NoCache($GLOBALS["AuthId"])',
  '$DefaultGroup' => '$GLOBALS["DefaultGroup"]',
  '$DefaultName'  => '$GLOBALS["DefaultName"]',
  '$BaseName'     => 'MakeBaseName($pn)',
  '$Action'       => '$GLOBALS["action"]',
  '$PasswdRead'   => 'PasswdVar($pn, "read")',
  '$PasswdEdit'   => 'PasswdVar($pn, "edit")',
  '$PasswdAttr'   => 'PasswdVar($pn, "attr")',
  );
$SaveProperties = array('title', 'description', 'keywords');
$PageTextVarPatterns = array(
  'var:'        => '/^(:*[ \\t]*(\\w[-\\w]*)[ \\t]*:[ \\t]?)(.*)($)/m',
  '(:var:...:)' => '/(\\(: *(\\w[-\\w]*) *:(?!\\))\\s?)(.*?)(:\\))/s'
  );


$WikiTitle = 'PmWiki';
$Charset = 'ISO-8859-1';
$HTTPHeaders = array(
  "Expires: Tue, 01 Jan 2002 00:00:00 GMT",
  "Cache-Control: no-store, no-cache, must-revalidate",
  "Content-type: text/html; charset=ISO-8859-1;");
$CacheActions = array('browse','diff','print');
$EnableHTMLCache = 0;
$NoHTMLCache = 0;
$HTMLTagAttr = '';
$HTMLDoctypeFmt = 
  "<!DOCTYPE html 
    PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
  <html xmlns='http://www.w3.org/1999/xhtml' \$HTMLTagAttr><head>\n";
$HTMLStylesFmt['pmwiki'] = "
  ul, ol, pre, dl, p { margin-top:0px; margin-bottom:0px; }
  code.escaped { white-space: pre; }
  .vspace { margin-top:1.33em; }
  .indent { margin-left:40px; }
  .outdent { margin-left:40px; text-indent:-40px; }
  a.createlinktext { text-decoration:none; border-bottom:1px dotted gray; }
  a.createlink { text-decoration:none; position:relative; top:-0.5em;
    font-weight:bold; font-size:smaller; border-bottom:none; }
  img { border:0px; }
  ";
$HTMLHeaderFmt['styles'] = array(
  "<style type='text/css'><!--",&$HTMLStylesFmt,"\n--></style>");
$HTMLBodyFmt = "</head>\n<body>";
$HTMLStartFmt = array('headers:',&$HTMLDoctypeFmt,&$HTMLHeaderFmt,
  &$HTMLBodyFmt);
$HTMLEndFmt = "\n</body>\n</html>";
$PageStartFmt = array(&$HTMLStartFmt,"\n<div id='contents'>\n");
$PageEndFmt = array('</div>',&$HTMLEndFmt);

$HandleActions = array(
  'browse' => 'HandleBrowse', 'print' => 'HandleBrowse',
  'edit' => 'HandleEdit', 'source' => 'HandleSource', 
  'attr' => 'HandleAttr', 'postattr' => 'HandlePostAttr',
  'logout' => 'HandleLogoutA', 'login' => 'HandleLoginA');
$HandleAuth = array(
  'browse' => 'read', 'source' => 'read', 'print' => 'read',
  'edit' => 'edit', 'attr' => 'attr', 'postattr' => 'attr',
  'logout' => 'read', 'login' => 'login');
$ActionTitleFmt = array(
  'edit' => '| $[Edit]',
  'attr' => '| $[Attributes]',
  'login' => '| $[Login]');
$DefaultPasswords = array('admin'=>'@lock','read'=>'','edit'=>'','attr'=>'');
$AuthCascade = array('edit'=>'read', 'attr'=>'edit');
$AuthList = array('' => 1, 'nopass:' => 1, '@nopass' => 1);
$SessionEncode = 'base64_encode';
$SessionDecode = 'base64_decode';

$CallbackFnTemplates = array(
  'default' => '%s',
  'return' => 'return %s;',
  'markup_e' => 'extract($GLOBALS["MarkupToHTML"]); return %s;',
  'qualify'  => 'extract($GLOBALS["tmp_qualify"]); return %s;',
);

$Conditions['enabled'] = '(boolean)@$GLOBALS[$condparm]';
$Conditions['false'] = 'false';
$Conditions['true'] = 'true';
$Conditions['group'] = 
  "(boolean)MatchPageNames(\$pagename, FixGlob(\$condparm, '$1$2.*'))";
$Conditions['name'] = 
  "(boolean)MatchPageNames(\$pagename, FixGlob(\$condparm, '$1*.$2'))";
$Conditions['match'] = 'preg_match("!$condparm!",$pagename)';
$Conditions['authid'] = 'NoCache(@$GLOBALS["AuthId"] > "")';
$Conditions['equal'] = 'CompareArgs($condparm) == 0';
function CompareArgs($arg) 
  { $arg = ParseArgs($arg); return strcmp(@$arg[''][0], @$arg[''][1]); }

$Conditions['auth'] = 'NoCache(CondAuth($pagename, $condparm))';
function CondAuth($pagename, $condparm) {
  global $HandleAuth;
  @list($level, $pn) = explode(' ', $condparm, 2);
  $pn = ($pn > '') ? MakePageName($pagename, $pn) : $pagename;
  if (@$HandleAuth[$level]>'') $level = $HandleAuth[$level];
  return (boolean)RetrieveAuthPage($pn, $level, false, READPAGE_CURRENT);
}
$Conditions['exists'] = 'CondExists($condparm)';
## This is an optimized version of the earlier conditional
## especially for pagelists
function CondExists($condparm, $caseinsensitive = true) {
  static $ls = false;
  if(!$ls) $ls = ListPages();
  $condparm = str_replace(array('[[',']]'), array('', ''), $condparm);
  $glob = FixGlob($condparm, '$1*.$2');
  return (boolean)MatchPageNames($ls, $glob, $caseinsensitive);
}

## CondExpr handles complex conditions (expressions)
## Portions Copyright 2005 by D. Faure (dfaure@cpan.org)
function CondExpr($pagename, $condname, $condparm) {
  global $CondExprOps;
  SDV($CondExprOps, 'and|x?or|&&|\\|\\||[!()]');
  if ($condname == '(' || $condname == '[')
    $condparm = preg_replace('/[\\]\\)]\\s*$/', '', $condparm);
  $condparm = str_replace('&amp;&amp;', '&&', $condparm);
  $terms = preg_split("/(?<!\\S)($CondExprOps)(?!\\S)/i", $condparm, -1,
                      PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
  foreach($terms as $i => $t) {
    $t = trim($t);
    if (preg_match("/^($CondExprOps)$/i", $t)) continue;
    if ($t) $terms[$i] = CondText($pagename, "if $t", 'TRUE') ? '1' : '0';
  }
  return @eval('return(' . implode(' ', $terms) . ');');
}
$Conditions['expr'] = 'CondExpr($pagename, $condname, $condparm)';
$Conditions['('] = 'CondExpr($pagename, $condname, $condparm)';
$Conditions['['] = 'CondExpr($pagename, $condname, $condparm)';

$MarkupTable['_begin']['seq'] = 'B';
$MarkupTable['_end']['seq'] = 'E';
Markup('fulltext','>_begin');
Markup('split','>fulltext',"\n",
  '$RedoMarkupLine=1; return explode("\n",$x);');
Markup('directives','>split');
Markup('inline','>directives');
Markup('links','>inline');
Markup('block','>links');
Markup('style','>block');
Markup('closeall', '_begin',
  '/^\\(:closeall:\\)$/', "MarkupMarkupClose");
function MarkupMarkupClose() { return '<:block>' . MarkupClose(); }

$ImgExtPattern="\\.(?:gif|jpg|jpeg|a?png|svgz?|GIF|JPG|JPEG|A?PNG|SVGZ?|webp|WEBP)";
$ImgTagFmt="<img src='\$LinkUrl' alt='\$LinkAlt' title='\$LinkAlt' />";

$BlockMarkups = array(
  'block' => array('','','',0),
  'ul' => array('<ul><li>','</li><li>','</li></ul>',1),
  'dl' => array('<dl>','</dd>','</dd></dl>',1),
  'ol' => array('<ol><li>','</li><li>','</li></ol>',1),
  'p' => array('<p>','','</p>',0),
  'indent' => 
     array("<div class='indent'>","</div><div class='indent'>",'</div>',1),
  'outdent' => 
     array("<div class='outdent'>","</div><div class='outdent'>",'</div>',1),
  'pre' => array('<pre>','','</pre>',0),
  'table' => array("<table width='100%'>",'','</table>',0));

foreach(array('http:','https:','mailto:','ftp:','news:','gopher:','nap:',
    'file:', 'tel:', 'geo:') as $m)
  { $LinkFunctions[$m] = 'LinkIMap';  $IMap[$m]="$m$1"; }
$LinkFunctions['<:page>'] = 'LinkPage';

$q = preg_replace('/(\\?|%3f)([-\\w]+=)/i', '&$2', @$_SERVER['QUERY_STRING']);
if ($q != @$_SERVER['QUERY_STRING']) {
  unset($_GET);
  parse_str($q, $_GET);
  $_REQUEST = array_merge($_REQUEST, $_GET, $_POST);
}

if (isset($_GET['action'])) $action = $_GET['action'];
elseif (isset($_POST['action'])) $action = $_POST['action'];
else $action = 'browse';

$pagename = @$_REQUEST['n'];
if (!$pagename) $pagename = @$_REQUEST['pagename'];
if (!$pagename && 
    preg_match('!^'.preg_quote($_SERVER['SCRIPT_NAME'],'!').'/?([^?]*)!',
      $_SERVER['REQUEST_URI'],$match))
  $pagename = urldecode($match[1]);
if (preg_match('/[\\x80-\\xbf]/',$pagename)) 
  $pagename=utf8_decode($pagename);
$pagename = preg_replace('![^[:alnum:]\\x80-\\xff]+$!','',$pagename);
$pagename_unfiltered = $pagename;
$pagename = preg_replace('![${}\'"\\\\]+!', '', $pagename);
$FmtPV['$RequestedPage'] = 'PHSC($GLOBALS["pagename_unfiltered"], ENT_QUOTES)';
$Cursor['*'] = &$pagename;
if (function_exists("date_default_timezone_get") ) { # fix PHP5.3 warnings
  @date_default_timezone_set(@date_default_timezone_get());
}

$DenyHtaccessContent = <<<EOF
<IfModule !mod_authz_host.c>
  Order Deny,Allow
  Deny from all
</IfModule>

<IfModule mod_authz_host.c>
  Require all denied
</IfModule>

EOF;

if (file_exists("$FarmD/local/farmconfig.php")) 
  include_once("$FarmD/local/farmconfig.php");
if (IsEnabled($EnableLocalConfig,1)) {
  if (file_exists("$LocalDir/config.php")) 
    include_once("$LocalDir/config.php");
  elseif (file_exists('config.php'))
    include_once('config.php');
}

SDV($CurrentTime, strftime($TimeFmt, $Now));
SDV($CurrentTimeISO, strftime($TimeISOFmt, $Now));

if (IsEnabled($EnableStdConfig,1))
  include_once("$FarmD/scripts/stdconfig.php");

if (isset($PostConfig) && is_array($PostConfig)) {
  asort($PostConfig, SORT_NUMERIC);
  foreach ($PostConfig as $k=>$v) {
    if (!$k || !$v || $v<50) continue;
    if (function_exists($k)) $k($pagename);
    elseif (file_exists($k)) include_once($k);
  }
}

function pmsetcookie($name, $val="", $exp=0, $path="", $dom="", $secure=null, $httponly=null) {
  global $EnableCookieSecure, $EnableCookieHTTPOnly, $SetCookieFunction;
  if(IsEnabled($SetCookieFunction))
    return $SetCookieFunction($name, $val, $exp, $path, $dom, $secure, $httponly);
  if (is_null($secure))   $secure   = IsEnabled($EnableCookieSecure,   false);
  if (is_null($httponly)) $httponly = IsEnabled($EnableCookieHTTPOnly, false);
  setcookie($name, $val, $exp, $path, $dom, $secure, $httponly);
}
if (IsEnabled($EnableCookieSecure, false)) 
    @ini_set('session.cookie_secure', $EnableCookieSecure);
if (IsEnabled($EnableCookieHTTPOnly, false)) 
    @ini_set('session.cookie_httponly', $EnableCookieHTTPOnly);

foreach((array)$InterMapFiles as $f) {
  $f = FmtPageName($f, $pagename);
  if (($v = @file($f))) 
    $v = preg_replace('/^\\s*(?>\\w[-\\w]*)(?!:)/m', '$0:', implode('', $v));
  else if (@PageExists($f)) {
    $p = ReadPage($f, READPAGE_CURRENT);
    $v = @$p['text'];
  } else continue;
  if (!preg_match_all("/^\\s*(\\w[-\\w]*:)[^\\S\n]+(\\S*)/m", $v, 
                      $match, PREG_SET_ORDER)) continue;
  foreach($match as $m) {
    if (strpos($m[2], '$1') === false) $m[2] .= '$1';
    $LinkFunctions[$m[1]] = 'LinkIMap';
    $IMap[$m[1]] = FmtPageName($m[2], $pagename);
  }
}

$keys = array_keys($AuthCascade);
while ($keys) {
  $k = array_shift($keys); $t = $AuthCascade[$k];
  if (in_array($t, $keys)) 
    { unset($AuthCascade[$k]); $AuthCascade[$k] = $t; array_push($keys, $k); }
}

$LinkPattern = implode('|',array_keys($LinkFunctions));  # after InterMaps
SDV($LinkPageCreateSpaceFmt,$LinkPageCreateFmt);
$ActionTitle = FmtPageName(@$ActionTitleFmt[$action], $pagename);


if (!@$HandleActions[$action] || !function_exists($HandleActions[$action])) 
  $action='browse';
if (IsEnabled($EnableActions, 1)) HandleDispatch($pagename, $action);
Lock(0);
return;

##  HandleDispatch() is used to dispatch control to the appropriate
##  action handler with the appropriate permissions.
##  If a message is supplied, it is added to $MessagesFmt.
function HandleDispatch($pagename, $action, $msg=NULL) {
  global $MessagesFmt, $HandleActions, $HandleAuth;
  if ($msg) $MessagesFmt[] = "<div class='wikimessage'>$msg</div>";
  $fn = $HandleActions[$action];
  $auth = @$HandleAuth[$action];
  if (!$auth) $auth = 'read';
  return $fn($pagename, $auth);
}

## helper functions
function stripmagic($x) {
  $fn = 'get_magic_quotes_gpc';
  if (!function_exists($fn)) return $x;
  if (is_array($x)) {
    foreach($x as $k=>$v) $x[$k] = stripmagic($v);
    return $x;
  }
  return @$fn() ? stripslashes($x) : $x;
}
function pre_r(&$x)
  { return '<pre>'.PHSC(print_r($x, true)).'</pre>'; }
function PSS($x) 
  { return str_replace('\\"','"',$x); }
function PVS($x) 
  { return preg_replace("/\n[^\\S\n]*(?=\n)/", "\n<:vspace>", $x); }
function PVSE($x) { return PVS(PHSC($x, ENT_NOQUOTES)); }
function PZZ($x,$y='') { return ''; }
function PRR($x=NULL) 
  { if ($x || is_null($x)) $GLOBALS['RedoMarkupLine']++; return $x; }
function PUE($x)
  { return preg_replace_callback('/[\\x80-\\xff \'"<>]/', "cb_pue", $x); }
function cb_pue($m) { return '%'.dechex(ord($m[0])); }
function PQA($x, $keep=true) { 
  $out = '';
  if (preg_match_all('/([a-zA-Z][-\\w]*)\\s*=\\s*("[^"]*"|\'[^\']*\'|\\S*)/',
                     $x, $attr, PREG_SET_ORDER)) {
    foreach($attr as $a) {
      if (preg_match('/^on/i', $a[1])) continue;
      $val = preg_replace('/^([\'"]?)(.*)\\1$/', '$2', $a[2]);
      if ($keep) $val = Keep(PHSC($val, ENT_QUOTES, null, false));
      else $val = str_replace("'", '&#39;', $val);
      
      $out .= "{$a[1]}='$val' ";
    }
  }
  return $out;
}
function SDV(&$v,$x) { if (!isset($v)) $v=$x; }
function SDVA(&$var,$val) 
  { foreach($val as $k=>$v) if (!isset($var[$k])) $var[$k]=$v; }
function IsEnabled(&$var,$f=0)
  { return (isset($var)) ? $var : $f; }
function SetTmplDisplay($var, $val) 
  { NoCache(); $GLOBALS['TmplDisplay'][$var] = $val; }
function NoCache($x = '') { $GLOBALS['NoHTMLCache'] |= 1; return $x; }
function ParseArgs($x, $optpat = '(?>(\\w+)[:=])') {
  $z = array();
  preg_match_all("/($optpat|[-+])?(\"[^\"]*\"|'[^']*'|\\S+)/",
    $x, $terms, PREG_SET_ORDER);
  foreach($terms as $t) {
    $v = preg_replace('/^([\'"])?(.*)\\1$/', '$2', $t[3]);
    if ($t[2]) { $z['#'][] = $t[2]; $z[$t[2]] = $v; }
    else { $z['#'][] = $t[1]; $z[$t[1]][] = $v; }
    $z['#'][] = $v;
  }
  return $z;
}
function PHSC($x, $flags=ENT_COMPAT, $enc=null, $dbl_enc=true) { # for PHP 5.4
  if (is_null($enc)) $enc = "ISO-8859-1"; # single-byte charset
  if (! is_array($x)) return @htmlspecialchars($x, $flags, $enc, $dbl_enc);
  foreach($x as $k=>$v) $x[$k] = PHSC($v, $flags, $enc, $dbl_enc);
  return $x;
}
function PCCF($code, $template = 'default', $args = '$m') {
  global $CallbackFnTemplates, $CallbackFunctions, $PCCFOverrideFunction;
  if ($PCCFOverrideFunction && is_callable($PCCFOverrideFunction))
    return $PCCFOverrideFunction($code, $template, $args);

  if (!isset($CallbackFnTemplates[$template]))
    Abort("No \$CallbackFnTemplates[$template]).");
  $code = sprintf($CallbackFnTemplates[$template], $code);
  if (!isset($CallbackFunctions[$code])) {
    $fn = create_function($args, $code); # called by old addon|skin|recipe needing update, see pmwiki.org/Troubleshooting
    if ($fn) $CallbackFunctions[$code] = $fn;
    else StopWatch("Failed to create callback function: ".PHSC($code));
  }
  return $CallbackFunctions[$code];
}
function PPRE($pat, $rep, $x) {
  if(! function_exists('create_function')) return $x;
  $lambda = PCCF("return $rep;");
  return preg_replace_callback($pat, $lambda, $x);
}
function PPRA($array, $x) {
  foreach((array)$array as $pat => $rep) {
    # skip broken patterns rather than crash the PHP installation
    $oldpat = preg_match('!^/.+/[^/]*e[^/]*$!', $pat);
    if($oldpat && PHP_VERSION_ID >= 50500) continue;
    
    $fmt = $x; # for $FmtP
    if (is_callable($rep) && $rep != '_') 
      $x = preg_replace_callback($pat,$rep,$x);
    else
      $x = preg_replace($pat,$rep,$x);# simple text OR called by old addon|skin|recipe needing update, see pmwiki.org/Troubleshooting
  }
  return $x;
}
## callback functions
class PPRC { # PmWiki preg replace callbacks + pass local vars
  var $vars;
  function __construct($vars = false) {
    if ($vars && !is_null($vars)) $this->vars = $vars;
  }
  function pagevar($m) { # called from FmtPageName
    $pagename = $this->vars;
    return PageVar($pagename, $m[1]);
  }
}

# restores kept/protected strings
function cb_expandkpv($m) { return $GLOBALS['KPV'][$m[1]]; }

# make a string upper or lower case in various patterns
function cb_toupper($m) { return strtoupper($m[1]); }
function cb_tolower($m) { return strtolower($m[1]); }

function pmcrypt($str, $salt=null) {
  if ($salt && preg_match('/^(-?@|\\*$)/',  $salt)) return false;
  if (!is_null($salt)) return crypt($str, $salt);

  if (function_exists('password_hash'))
    return password_hash($str, PASSWORD_DEFAULT);
  return crypt($str);
}

# generate or check a random one-time token to prevent CSRF
function pmtoken($token = null) {
  global $SessionMaxTokens, $PmTokenFn;
  if(IsEnabled($PmTokenFn) && function_exists($PmTokenFn))
    return $PmTokenFn($token);
  @session_start();
  if(!isset($_SESSION['pmtokens'])) $_SESSION['pmtokens'] = array();
  if(is_null($token)) { # create a one-time token
    $len = mt_rand(20,30);
    $token = "";
    while(strlen($token)<$len) {
      $token .= chr(mt_rand(32,126));
    }
    if(count($_SESSION['pmtokens']))
      $id = max(array_keys($_SESSION['pmtokens']))+1;
    else $id = 0;
    $_SESSION['pmtokens'][$id] = $token;
    if(IsEnabled($SessionMaxTokens, 0)) {
      $max = $SessionMaxTokens;
      $_SESSION['pmtokens'] = array_slice($_SESSION['pmtokens'], -$max, $max, true);
    }
    return "$id:" . md5($token);
  }
  # else: check a token, if correct, delete it
  @list($id, $hash) = explode(':', $token);
  $id = intval($id);
  if(isset($_SESSION['pmtokens'][$id]) && $hash == md5($_SESSION['pmtokens'][$id])) {
    unset($_SESSION['pmtokens'][$id]);
    return true;
  }
  return false;
}

function StopWatch($x) { 
  global $StopWatch, $EnableStopWatch;
  if (!$EnableStopWatch) return;
  static $wstart = 0, $ustart = 0;
  list($usec,$sec) = explode(' ',microtime());
  $wtime = ($sec+$usec); 
  if (!$wstart) $wstart = $wtime;
  if ($EnableStopWatch != 2) 
    { $StopWatch[] = sprintf("%05.2f %s", $wtime-$wstart, $x); return; }
  $dat = getrusage();
  $utime = ($dat['ru_utime.tv_sec']+$dat['ru_utime.tv_usec']/1000000);
  if (!$ustart) $ustart=$utime;
  $StopWatch[] = 
    sprintf("%05.2f %05.2f %s", $wtime-$wstart, $utime-$ustart, $x);
}


## DRange converts a variety of string formats into date (ranges).
## It returns the start and end timestamps (+1 second) of the specified date.
function DRange($when) {
  global $Now;
  ##  unix/posix @timestamp dates
  if (preg_match('/^\\s*@(\\d+)\\s*(.*)$/', $when, $m)) {
    $t0 = $m[2] ? strtotime($m[2], $m[1]) : $m[1];
    return array($t0, $t0+1);
  }
  ##  ISO-8601 dates
  $dpat = '/
    (?<!\\d)                 # non-digit
    (19\\d\\d|20[0-3]\\d)    # year ($1)
    ([-.\\/]?)               # date separator ($2)
    (0\\d|1[0-2])            # month ($3)
    (?: \\2                  # repeat date separator
      ([0-2]\\d|3[0-1])      # day ($4)
      (?: T                  # time marker
        ([01]\\d|2[0-4])     # hour ($5)
        ([.:]?)              # time separator ($6)
        ([0-5]\\d)           # minute ($7)
        (?: \\6              # repeat time separator
          ([0-5]\\d|60)      # seconds ($8)
        )?                   # optional :ss
      )?                     # optional Thh:mm:ss
    )?                       # optional -ddThh:mm:ss
    (?!\d)                   # non-digit
    /x';
  if (preg_match($dpat, $when, $m) &&
    !preg_match('/[+-]\\s*\\d+\\s*(sec(ond)?|min(ute)?|forth?night|day|week(day)?|month|year)s?/i', $when)) {
    $n = $m;
    ##  if no time given, assume range of 1 day (except when full month)
    if (@$m[4]>'' && @$m[5] == '') { @$n[4]++; }
    ##  if no day given, assume 1st of month and full month range
    if (@$m[4] == '') { $m[4] = 1; $n[4] = 1; $n[3]++; }
    ##  if no seconds given, assume range of 1 minute (except when full day)
    if (@$m[7]>'' && @$m[8] == '') { @$n[7]++; }
    $t0 = @mktime($m[5], $m[7], $m[8], $m[3], $m[4], $m[1]);
    $t1 = @mktime($n[5], $n[7], $n[8], $n[3], $n[4], $n[1]);
    return array($t0, $t1);
  }
  ##  now, today, tomorrow, yesterday
  NoCache();
  if ($when == 'now') return array($Now, $Now+1);
  $m = localtime(time());
  if ($when == 'tomorrow') { $m[3]++; $when = 'today'; }
  if ($when == 'yesterday') { $m[3]--; $when = 'today'; }
  if ($when == 'today') 
    return array(mktime(0, 0, 0, $m[4]+1, $m[3]  , $m[5]+1900),
                 mktime(0, 0, 0, $m[4]+1, $m[3]+1, $m[5]+1900));
  if (preg_match('/^\\s*$/', $when)) return array(-1, -1);
  $t0 = strtotime($when);
  $t1 = strtotime("+1 day", $t0);
  return array($t0, $t1);
}

## DiffTimeCompact subtracts 2 timestamps and outputs a compact
## human-readable delay in hours, days, weeks, months or years
function DiffTimeCompact($time, $time2=null, $precision=1) {
  if(is_null($time2)) $time2 = $GLOBALS['Now'];
  $suffix = explode(',', XL('h,d,w,m,y'));
  $x = $hours = abs($time2 - $time)/3600;
  if($x<24) return round($x,$precision).$suffix[0];
  $x /= 24;   if($x<14) return round($x,$precision).$suffix[1];
  $x /= 7;    if($x< 9) return round($x,$precision).$suffix[2];
  $x = $hours/2/365.2425; if($x<24) return round($x,$precision).$suffix[3];
  return round($hours/24/365.2425,$precision).$suffix[4];
}

## FileSizeCompact outputs a human readable file size
## with an appropriate suffix.
## Note: unreliable filemtime()/stat() over 2GB @ 32bit
function FileSizeCompact($n, $precision=1) {
  if(!(float)$n) return 0;
  $units = 'bkMGTPEZY';
  $b = log((float)$n, 1024);
  $fb = floor($b);
  return round(pow(1024,$b-$fb),$precision).@$units[$fb];
}

## AsSpaced converts a string with WikiWords into a spaced version
## of that string.  (It can be overridden via $AsSpacedFunction.)
function AsSpaced($text) {
  $text = preg_replace("/([[:lower:]\\d])([[:upper:]])/", '$1 $2', $text);
  $text = preg_replace('/([^-\\d])(\\d[-\\d]*( |$))/','$1 $2',$text);
  return preg_replace("/([[:upper:]])([[:upper:]][[:lower:]\\d])/",
    '$1 $2', $text);
}

## Lock is used to make sure only one instance of PmWiki is running when
## files are being written.  It does not "lock pages" for editing.
function Lock($op) { 
  global $WorkDir, $LockFile, $EnableReadOnly;
  if ($op > 0 && IsEnabled($EnableReadOnly, 0))
    Abort('Cannot modify site -- $EnableReadOnly is set', 'readonly');
  SDV($LockFile, "$WorkDir/.flock");
  mkdirp(dirname($LockFile));
  static $lockfp,$curop;
  if (!$lockfp) $lockfp = @fopen($LockFile, "w");
  if (!$lockfp) { 
    if ($op <= 0) return;
    @unlink($LockFile); 
    $lockfp = fopen($LockFile,"w") or
      Abort('Cannot acquire lockfile', 'flock');
    fixperms($LockFile);
  }
  if ($op<0) { flock($lockfp,LOCK_UN); fclose($lockfp); $lockfp=0; $curop=0; }
  elseif ($op==0) { flock($lockfp,LOCK_UN); $curop=0; }
  elseif ($op==1 && $curop<1) 
    { session_write_close(); flock($lockfp,LOCK_SH); $curop=1; }
  elseif ($op==2 && $curop<2) 
    { session_write_close(); flock($lockfp,LOCK_EX); $curop=2; }
}

## mkdirp creates a directory and its parents as needed, and sets
## permissions accordingly.
function mkdirp($dir) {
  global $ScriptUrl;
  if (file_exists($dir)) return;
  if (!file_exists(dirname($dir))) mkdirp(dirname($dir));
  if (mkdir($dir, 0777)) {
    fixperms($dir);
    if (@touch("$dir/xxx")) { unlink("$dir/xxx"); return; }
    rmdir($dir);
  }
  $parent = realpath(dirname($dir)); 
  $bdir = basename($dir);
  $perms = decoct(fileperms($parent) & 03777);
  $msg = "PmWiki needs to have a writable <tt>$dir/</tt> directory 
    before it can continue.  You can create the directory manually 
    by executing the following commands on your server:
    <pre>    mkdir $parent/$bdir\n    chmod 777 $parent/$bdir</pre>
    Then, <a href='{$ScriptUrl}'>reload this page</a>.";
  $safemode = ini_get('safe_mode');
  if (!$safemode) $msg .= "<br /><br />Or, for a slightly more 
    secure installation, try executing <pre>    chmod 2777 $parent</pre> 
    on your server and following <a target='_blank' href='$ScriptUrl'>
    this link</a>.  Afterwards you can restore the permissions to 
    their current setting by executing <pre>    chmod $perms $parent</pre>.";
  Abort($msg);
}

## fixperms attempts to correct permissions on a file or directory
## so that both PmWiki and the account (current dir) owner can manipulate it
function fixperms($fname, $add = 0, $set = 0) {
  clearstatcache();
  if (!file_exists($fname)) Abort('?no such file');
  if ($set) { # advanced admins, $UploadPermSet
    if (fileperms($fname) != $set) @chmod($fname,$set);
  }
  else {
    $bp = 0;
    if (fileowner($fname)!=@fileowner('.') && @fileowner('.')!==0)
      $bp = (is_dir($fname)) ? 007 : 006;
    if (filegroup($fname)==@filegroup('.')) $bp <<= 3;
    $bp |= $add;
    if ($bp && (fileperms($fname) & $bp) != $bp)
      @chmod($fname,fileperms($fname)|$bp);
  }
}

## GlobToPCRE converts wildcard patterns into pcre patterns for
## inclusion and exclusion.  Wildcards beginning with '-' or '!'
## are treated as things to be excluded.
function GlobToPCRE($pat) {
  $pat = preg_quote($pat, '/');
  $pat = str_replace(array('\\*', '\\?', '\\[', '\\]', '\\^', '\\-', '\\!'),
                     array('.*',  '.',   '[',   ']',   '^', '-', '!'), $pat);
  $excl = array(); $incl = array();
  foreach(preg_split('/,+\s?/', $pat, -1, PREG_SPLIT_NO_EMPTY) as $p) {
    if ($p[0] == '-' || $p[0] == '!') $excl[] = '^'.substr($p, 1).'$';
    else $incl[] = "^$p$";
  }
  return array(implode('|', $incl), implode('|', $excl));
}

## FixGlob changes wildcard patterns without '.' to things like
## '*.foo' (name matches) or 'foo.*' (group matches).
function FixGlob($x, $rep = '$1*.$2') {
  return preg_replace('/([\\s,][-!]?)([^\\/.\\s,]+)(?=[\\s,])/', $rep, ",$x,");
}

## MatchPageNames reduces $pagelist to those pages with names
## matching the pattern(s) in $pat.  Patterns can be either
## regexes to include ('/'), regexes to exclude ('!'), or
## wildcard patterns (all others).
function MatchPageNames($pagelist, $pat, $caseinsensitive = true) {
  # Note: MatchNames() is the generic function matching patterns,
  # works for attachments and other arrays. We can commit to 
  # keep it generic, even if we someday change MatchPageNames().
  return MatchNames($pagelist, $pat, $caseinsensitive);
}
function MatchNames($list, $pat, $caseinsensitive = true) {
  global $Charset, $EnableRangeMatchUTF8;
  # allow range matches in utf8; doesn't work on pmwiki.org and possibly elsewhere
  $pcre8 = (IsEnabled($EnableRangeMatchUTF8,0) && $Charset=='UTF-8')? 'u' : '';
  $insensitive = $caseinsensitive ? 'i' : '';
  $list = (array)$list;
  foreach((array)$pat as $p) {
    if (count($list) < 1) break;
    if (!$p) continue;
    switch ($p[0]) {
      case '/': 
        $list = preg_grep($p, $list); 
        break;
      case '!':
        $list = array_diff($list, preg_grep($p, $list)); 
        break;
      default:
        list($inclp, $exclp) = GlobToPCRE(str_replace('/', '.', $p));
        if ($exclp) 
          $list = array_diff($list, preg_grep("/$exclp/$insensitive$pcre8", $list));
        if ($inclp)
          $list = preg_grep("/$inclp/$insensitive$pcre8", $list);
    }
  }
  return $list;
}
  
## ResolvePageName "normalizes" a pagename based on the current
## settings of $DefaultPage and $PagePathFmt.  It's normally used
## during initialization to fix up any missing or partial pagenames.
function ResolvePageName($pagename) {
  global $DefaultPage, $DefaultGroup, $DefaultName,
    $GroupPattern, $NamePattern, $EnableFixedUrlRedirect;
  SDV($DefaultPage, "$DefaultGroup.$DefaultName");
  $pagename = preg_replace('!([./][^./]+)\\.html?$!', '$1', $pagename);
  if ($pagename == '') return $DefaultPage;
  $p = MakePageName($DefaultPage, $pagename);
  if (!preg_match("/^($GroupPattern)[.\\/]($NamePattern)$/i", $p)) {
    header('HTTP/1.1 404 Not Found');
    Abort("\$[?invalid page name] \"$p\"");
  }
  if (preg_match("/^($GroupPattern)[.\\/]($NamePattern)$/i", $pagename))
    return $p;
  if (IsEnabled($EnableFixedUrlRedirect, 1)
      && $p && (PageExists($p) || preg_match('/[\\/.]/', $pagename)))
    { Redirect($p); exit(); }
  return MakePageName($DefaultPage, "$pagename.$pagename");
}

## MakePageName is used to convert a string $str into a fully-qualified
## pagename.  If $str doesn't contain a group qualifier, then 
## MakePageName uses $basepage and $PagePathFmt to determine the 
## group of the returned pagename.
function MakePageName($basepage, $str) {
  global $MakePageNameFunction, $PageNameChars, $PagePathFmt,
    $MakePageNamePatterns, $MakePageNameSplitPattern;
  if (@$MakePageNameFunction) return $MakePageNameFunction($basepage, $str);
  SDV($PageNameChars,'-[:alnum:]');
  SDV($MakePageNamePatterns, array(
    "/'/" => '',                      # strip single-quotes
    "/[^$PageNameChars]+/" => ' ',    # convert everything else to space
    '/((^|[^-\\w])\\w)/' => 'cb_toupper', # CamelCase
    '/ /' => ''));
  SDV($MakePageNameSplitPattern, '/[.\\/]/');
  $str = preg_replace('/[#?].*$/', '', $str);
  $m = preg_split($MakePageNameSplitPattern, $str);
  if (count($m)<1 || count($m)>2 || $m[0]=='') return '';
  ##  handle "Group.Name" conversions
  if (@$m[1] > '') {
    $group = PPRA($MakePageNamePatterns, $m[0]);
    $name =  PPRA($MakePageNamePatterns, $m[1]);
    return "$group.$name";
  }
  $name = PPRA($MakePageNamePatterns, $m[0]);
  $isgrouphome = count($m) > 1;
  foreach((array)$PagePathFmt as $pg) {
    if ($isgrouphome && strncmp($pg, '$1.', 3) !== 0) continue;
    $pn = FmtPageName(str_replace('$1', $name, $pg), $basepage);
    if (PageExists($pn)) return $pn;
  }
  if ($isgrouphome) {
    foreach((array)$PagePathFmt as $pg) 
      if (strncmp($pg, '$1.', 3) == 0)
        return FmtPageName(str_replace('$1', $name, $pg), $basepage);
    return "$name.$name";
  }
  return preg_replace('/[^\\/.]+$/', $name, $basepage);
}


## MakeBaseName uses $BaseNamePatterns to return the "base" form
## of a given pagename -- i.e., stripping any recipe-defined
## prefixes or suffixes from the page.
function MakeBaseName($pagename, $patlist = NULL) {
  global $BaseNamePatterns;
  if (is_null($patlist)) $patlist = (array)@$BaseNamePatterns;
  foreach($patlist as $pat => $rep) 
    $pagename = preg_replace($pat, $rep, $pagename); # TODO
  return $pagename;
}


## PCache caches basic information about a page and its attributes--
## usually everything except page text and page history.  This makes
## for quicker access to certain values in PageVar below.
function PCache($pagename, $page) {
  global $PCache;
  foreach($page as $k=>$v) 
    if ($k!='text' && strpos($k,':')===false) $PCache[$pagename][$k]=$v;
}

## SetProperty saves a page property into $PCache.  For convenience
## it returns the $value of the property just set.  If $sep is supplied,
## then $value is appended to the current property (with $sep as
## as separator) instead of replacing it. If $keep is suplied and the 
## property already exists, then $value will be ignored.
function SetProperty($pagename, $prop, $value, $sep=NULL, $keep=NULL) {
  global $PCache, $KeepToken;
  NoCache();
  $prop = "=p_$prop";
  $value = preg_replace_callback("/$KeepToken(\\d.*?)$KeepToken/",
                        "cb_expandkpv", $value);
  if (!is_null($sep) && isset($PCache[$pagename][$prop]))
    $value = $PCache[$pagename][$prop] . $sep . $value;
  if (is_null($keep) || !isset($PCache[$pagename][$prop]))
    $PCache[$pagename][$prop] = $value;
  return $PCache[$pagename][$prop];
}


## PageTextVar loads a page's text variables (defined by
## $PageTextVarPatterns) into a page's $PCache entry, and returns
## the property associated with $var.
function PageTextVar($pagename, $var) {
  global $PCache, $PageTextVarPatterns, $MaxPageTextVars, $DefaultUnsetPageTextVars, $DefaultEmptyPageTextVars;
  SDV($MaxPageTextVars, 500);
  static $status;
  if (@$status["$pagename:$var"]++ > $MaxPageTextVars) return '';
  if (!@$PCache[$pagename]['=pagetextvars']) {
    $pc = &$PCache[$pagename];
    $pc['=pagetextvars'] = 1;
    $page = RetrieveAuthPage($pagename, 'read', false, READPAGE_CURRENT);
    if ($page) {
      foreach((array)$PageTextVarPatterns as $pat) 
        if (preg_match_all($pat, IsEnabled($pc['=preview'],@$page['text']),
          $match, PREG_SET_ORDER))
          foreach($match as $m) {
            $t = preg_replace("/\\{\\$:{$m[2]}\\}/", '', $m[3]);
            $pc["=p_{$m[2]}"] = Qualify($pagename, $t);
          }
    }
  }
  if (! isset($PCache[$pagename]["=p_$var"]) && is_array($DefaultUnsetPageTextVars)) {
    foreach($DefaultUnsetPageTextVars as $k=>$v) {
      if (count(MatchNames($var, $k))) {
        $PCache[$pagename]["=p_$var"] = $v;
        break;
      }
    }
    SDV($PCache[$pagename]["=p_$var"], ''); # to avoid re-loop
  }
  elseif (@$PCache[$pagename]["=p_$var"] == '' && is_array($DefaultEmptyPageTextVars)) {
    foreach($DefaultEmptyPageTextVars as $k=>$v) {
      if (count(MatchNames($var, $k))) {
        $PCache[$pagename]["=p_$var"] = $v;
        break;
      }
    }
    SDV($PCache[$pagename]["=p_$var"], ''); # to avoid re-loop
  }
  return @$PCache[$pagename]["=p_$var"];
}


function PageVar($pagename, $var, $pn = '') {
  global $Cursor, $PCache, $FmtPV, $AsSpacedFunction, $ScriptUrl,
    $EnablePathInfo;
  if ($var == '$ScriptUrl') return PUE($ScriptUrl);
  if ($pn) {
    $pn = isset($Cursor[$pn]) ? $Cursor[$pn] : MakePageName($pagename, $pn);
  } else $pn = $pagename;
  if ($pn) {
    if (preg_match('/^(.+)[.\\/]([^.\\/]+)$/', $pn, $match)
        && !isset($PCache[$pn]['time']) 
        && (!@$FmtPV[$var] || strpos($FmtPV[$var], '$page') !== false)) {
      $page = ReadPage($pn, READPAGE_CURRENT);
      PCache($pn, $page);
    }
    @list($d, $group, $name) = $match;
    $page = &$PCache[$pn];
    if (strpos(@$FmtPV[$var], '$authpage') !== false) {
      if (!isset($page['=auth']['read'])) {
        $x = RetrieveAuthPage($pn, 'read', false, READPAGE_CURRENT);
        if ($x) PCache($pn, $x);
      }
      if (@$page['=auth']['read']) $authpage = &$page;
    }
  } else { $group = ''; $name = ''; }
  if (@$FmtPV[$var]) return eval("return ({$FmtPV[$var]});");
  if (strncmp($var, '$:', 2)==0) return PageTextVar($pn, substr($var, 2));
  return '';
}

## FmtPageName handles $[internationalization] and $Variable 
## substitutions in strings based on the $pagename argument.
function FmtPageName($fmt, $pagename) {
  # Perform $-substitutions on $fmt relative to page given by $pagename
  global $GroupPattern, $NamePattern, $EnablePathInfo, $ScriptUrl,
    $GCount, $UnsafeGlobals, $FmtV, $FmtP, $FmtPV, $PCache, $AsSpacedFunction;
  if (strpos($fmt,'$')===false) return $fmt;
  $fmt = preg_replace_callback('/\\$([A-Z]\\w*Fmt)\\b/','cb_expandglobal',$fmt);
  $fmt = preg_replace_callback('/\\$\\[(?>([^\\]]+))\\]/',"cb_expandxlang",$fmt);
  $fmt = str_replace('{$ScriptUrl}', '$ScriptUrl', $fmt);
  $pprc = new PPRC($pagename);
  $fmt = preg_replace_callback('/\\{\\*?(\\$[A-Z]\\w+)\\}/', 
    array($pprc, 'pagevar'), $fmt);
  if (strpos($fmt,'$')===false) return $fmt;
  if ($FmtP) $fmt = PPRA($FmtP, $fmt); # FIXME
  static $pv, $pvpat;
  if ($pv != count($FmtPV)) {
    $pvpat = str_replace('$', '\\$', implode('|', array_keys($FmtPV)));
    $pv = count($FmtPV);
  }
  $fmt = preg_replace_callback("/($pvpat)\\b/", array($pprc, 'pagevar'), $fmt);
  $fmt = preg_replace_callback('!\\$ScriptUrl/([^?#\'"\\s<>]+)!',
    'cb_expandscripturl', $fmt);
  if (strpos($fmt,'$')===false) return $fmt;
  static $g;
  if ($GCount != count($GLOBALS)+count($FmtV)) {
    $g = array();
    foreach($GLOBALS as $n=>$v) {
      if (is_array($v) || is_object($v) ||
        isset($FmtV["\$$n"]) || in_array($n,$UnsafeGlobals)) continue;
      $g["\$$n"] = $v;
    }
    $GCount = count($GLOBALS)+count($FmtV);
    krsort($g); reset($g);
  }
  $fmt = str_replace(array_keys($g),array_values($g),$fmt);
  $fmt = preg_replace_callback('/(?>(\\$[[:alpha:]]\\w+))/',
          "cb_expandfmtv", $fmt);
  return $fmt;
}
function cb_expandglobal($m){ return @$GLOBALS[$m[1]]; }
function cb_expandxlang ($m){ return NoCache(XL($m[1])); }
function cb_expandfmtv ($m){ 
  return isset($GLOBALS['FmtV'][$m[1]]) ? $GLOBALS['FmtV'][$m[1]] : $m[1];
}
function cb_expandscripturl($m) {
  global $EnablePathInfo, $ScriptUrl;
  return (@$EnablePathInfo) ? "$ScriptUrl/" . PUE($m[1])
    : "$ScriptUrl?n=".str_replace('/','.',PUE($m[1]));
}


## FmtPageTitle returns the page title, or the page name.
## It localizes standard technical pages (RecentChanges...)
function FmtPageTitle($title, $name, $spaced=0) {
  if ($title>'') return str_replace("$", "&#036;", $title);
  global $SpaceWikiWords, $AsSpacedFunction;
  if (preg_match("/^(Site(Admin)?
    |(All)?(Site|Group)(Header|Footer|Attributes)
    |(Side|Left|Right)Bar
    |(Wiki)?Sand[Bb]ox
    |(All)?Recent(Changes|Uploads)|(Auth|Edit)Form
    |InterMap|PageActions|\\w+QuickReference|\\w+Templates
    |NotifyList|AuthUser|ApprovedUrls|(Block|Auth)List
    )$/x", $name) && $name != XL($name))
      return XL($name);
  return ($spaced || $SpaceWikiWords) ? $AsSpacedFunction($name) : $name;
}

## FmtTemplateVars uses $vars to replace all occurrences of 
## {$$key} in $text with $vars['key'].
function FmtTemplateVars($text, $vars, $pagename = NULL) {
  global $FmtPV, $EnableUndefinedTemplateVars;
  if ($pagename) {
    $pat = implode('|', array_map('preg_quote', array_keys($FmtPV)));
    $pprc = new PPRC($pagename);
    $text = preg_replace_callback("/\\{\\$($pat)\\}/",
              array($pprc, 'pagevar'), $text);
  }
  foreach(preg_grep('/^[\\w$]/', array_keys($vars)) as $k)
    if (!is_array($vars[$k]))
      $text = str_replace("{\$\$$k}", @$vars[$k], $text);
  if (! IsEnabled($EnableUndefinedTemplateVars, 0))
    $text = preg_replace("/\\{\\$\\$\\w+\\}/", '', $text);
  return $text;
}

## The XL functions provide translation tables for $[i18n] strings
## in FmtPageName().
function XL($key) {
  global $XL,$XLLangs;
  foreach($XLLangs as $l) if (isset($XL[$l][$key])) return $XL[$l][$key];
  return $key;
}
function XLSDV($lang,$a) {
  global $XL;
  foreach($a as $k=>$v) {
    if (!isset($XL[$lang][$k])) {
      if (preg_match('/^e_(rows|cols)$/', $k)) $v = intval($v);
      elseif (preg_match('/^ak_/', $k)) $v = @$v[0];
      $XL[$lang][$k]=$v;
    }
  }
}
function XLPage($lang,$p,$nohtml=false) {
  global $TimeFmt,$XLLangs,$FarmD, $EnableXLPageScriptLoad;
  $page = ReadPage($p, READPAGE_CURRENT);
  if (!$page) return;
  $text = preg_replace("/=>\\s*\n/",'=> ',@$page['text']);
  foreach(explode("\n",$text) as $l)
    if (preg_match('/^\\s*[\'"](.+?)[\'"]\\s*=>\\s*[\'"](.+)[\'"]/',$l,$m))
      $xl[stripslashes($m[1])] = stripslashes($nohtml? PHSC($m[2]): $m[2]);
  if (isset($xl)) {
    if (IsEnabled($EnableXLPageScriptLoad, 0) && @$xl['xlpage-i18n']) {
      $i18n = preg_replace('/[^-\\w]/','',$xl['xlpage-i18n']);
      include_once("$FarmD/scripts/xlpage-$i18n.php");
    }
    if (@$xl['Locale']) setlocale(LC_ALL,$xl['Locale']);
    if (@$xl['TimeFmt']) $TimeFmt=$xl['TimeFmt'];
    if (!in_array($lang, $XLLangs)) array_unshift($XLLangs, $lang);
    XLSDV($lang,$xl);
  }
}

## CmpPageAttr is used with uksort to order a page's elements with
## the latest items first.  This can make some operations more efficient.
function CmpPageAttr($a, $b) {
  @list($x, $agmt) = explode(':', $a);
  @list($x, $bgmt) = explode(':', $b);
  $nagmt = intval($agmt);
  $nbgmt = intval($bgmt);
  if ($agmt != $bgmt) 
    return ($nagmt==0 || $nbgmt==0) ? $nagmt - $nbgmt : $nbgmt - $nagmt;
  return strcmp($a, $b);
}

## class PageStore holds objects that store pages via the native
## filesystem.
class PageStore {
  var $dirfmt;
  var $iswrite;
  var $encodefilenames;
  var $attr;
  function __construct($d='$WorkDir/$FullName', $w=0, $a=NULL) { 
    $this->dirfmt = $d; $this->iswrite = $w; $this->attr = (array)$a;
    $GLOBALS['PageExistsCache'] = array();
  }
  function recodefn($s,$from,$to) {
    static $able;
    if(is_null($able)) {
      # can we rely on iconv() or on mb_convert_encoding() ?
      if (function_exists('iconv') && @iconv("UTF-8", "WINDOWS-1252//IGNORE", "te\xd0\xafst")=='test' )
        $able = 'iconv';
      elseif (function_exists('mb_convert_encoding') && @mb_convert_encoding("te\xd0\xafst", "WINDOWS-1252", "UTF-8")=="te?st")
        $able = 'mb';
    }
    switch ($able) {
      case "iconv":
        return @iconv($from,"$to//IGNORE",$s);
      case "mb":
        return @mb_convert_encoding($s,$to,$from);
    }
    if ($to=='UTF-8' && $from=='WINDOWS-1252') return utf8_decode($s);
    if ($from=='UTF-8' && $to=='WINDOWS-1252') return utf8_encode($s);
    return $s;
  }
  function pagefile($pagename) {
    global $FarmD;
    $dfmt = $this->dirfmt;
    if ($pagename > '') {
      $pagename = str_replace('/', '.', $pagename);
      if ($dfmt == 'wiki.d/{$FullName}')               # optimizations for
        return $this->PFE("wiki.d/$pagename");         # standard locations
      if ($dfmt == '$FarmD/wikilib.d/{$FullName}')     # 
        return $this->PFE("$FarmD/wikilib.d/$pagename");
      if ($dfmt == 'wiki.d/{$Group}/{$FullName}')
        return $this->PFE(preg_replace('/([^.]+).*/', 'wiki.d/$1/$0', $pagename));
    }
    return $this->PFE(FmtPageName($dfmt, $pagename));
  }
  function PFE($f) { # pagefile_encode
    if (!$this->encodefilenames) return $f;
    global $PageFileEncodeFunction;
    return $PageFileEncodeFunction($f);
  }
  function PFD($f) { # pagefile_decode
    if (!$this->encodefilenames) return $f;
    global $PageFileDecodeFunction;
    return $PageFileDecodeFunction($f);
  }
  function read($pagename, $since=0) {
    $newline = '';
    $urlencoded = false;
    $pagefile = $this->pagefile($pagename);
    if ($pagefile && ($fp=@fopen($pagefile, "r"))) {
      $page = $this->attr;
      while (!feof($fp)) {
        $line = @fgets($fp, 4096);
        while (substr($line, -1, 1) != "\n" && !feof($fp)) 
          { $line .= fgets($fp, 4096); }
        $line = rtrim($line);
        if ($urlencoded) $line = urldecode(str_replace('+', '%2b', $line));
        @list($k,$v) = explode('=', $line, 2);
        if (!$k) continue;
        if ($k == 'version') { 
          $ordered = (strpos($v, 'ordered=1') !== false); 
          $urlencoded = (strpos($v, 'urlencoded=1') !== false); 
          if (strpos($v, 'pmwiki-0.')!==false) $newline="\262";
        }
        if ($k == 'newline') { $newline = $v; continue; }
        if ($since > 0 && preg_match('/:(\\d+)/', $k, $m) && $m[1] < $since) {
          if (@$ordered) break;
          continue;
        }
        if ($newline) $v = str_replace($newline, "\n", $v);
        $page[$k] = $v;
      }
      fclose($fp);
    }
    return $this->recode($pagename, @$page);
  }
  function write($pagename,$page) {
    global $Now, $Version, $Charset, $EnableRevUserAgent, $PageExistsCache, $DenyHtaccessContent;
    $page['charset'] = $Charset;
    $page['name'] = $pagename;
    $page['time'] = $Now;
    $page['host'] = $_SERVER['REMOTE_ADDR'];
    $page['agent'] = @$_SERVER['HTTP_USER_AGENT'];
    if(IsEnabled($EnableRevUserAgent, 0)) $page["agent:$Now"] = $page['agent'];
    $page['rev'] = @$page['rev']+1;
    unset($page['version']); unset($page['newline']);
    uksort($page, 'CmpPageAttr');
    $s = false;
    $pagefile = $this->pagefile($pagename);
    $dir = dirname($pagefile); mkdirp($dir);
    if (!file_exists("$dir/.htaccess") && $fp = @fopen("$dir/.htaccess", "w")) 
      { fwrite($fp, $DenyHtaccessContent); fclose($fp); }
    if ($pagefile && ($fp=fopen("$pagefile,new","w"))) {
      $r0 = array('%', "\n", '<');
      $r1 = array('%25', '%0a', '%3c');
      $x = "version=$Version ordered=1 urlencoded=1\n";
      $s = true && fputs($fp, $x); $sz = strlen($x);
      foreach($page as $k=>$v) 
        if ($k > '' && $k[0] != '=') {
          $x = str_replace($r0, $r1, "$k=$v") . "\n";
          $s = $s && fputs($fp, $x); $sz += strlen($x);
        }
      $s = fclose($fp) && $s;
      $s = $s && (filesize("$pagefile,new") > $sz * 0.95);
      if (file_exists($pagefile)) $s = $s && unlink($pagefile);
      $s = $s && rename("$pagefile,new", $pagefile);
    }
    $s && fixperms($pagefile);
    if (!$s)
      Abort("Cannot write page to $pagename ($pagefile)...changes not saved");
    PCache($pagename, $page);
    unset($PageExistsCache[$pagename]); # PITS:01401
  }
  function exists($pagename) {
    if (!$pagename) return false;
    $pagefile = $this->pagefile($pagename);
    return ($pagefile && file_exists($pagefile));
  }
  function delete($pagename) {
    global $Now, $PageExistsCache;
    $pagefile = $this->pagefile($pagename);
    @rename($pagefile,"$pagefile,del-$Now");
    unset($PageExistsCache[$pagename]); # PITS:01401
  }
  function ls($pats=NULL) {
    global $GroupPattern, $NamePattern;
    StopWatch("PageStore::ls begin {$this->dirfmt}");
    $pats=(array)$pats; 
    array_push($pats, "/^$GroupPattern\.$NamePattern$/");
    $dir = $this->pagefile('$Group.$Name');
    $maxslash = substr_count($dir, '/');
    $dirlist = array(preg_replace('!/*[^/]*\\$.*$!','',$dir));
    $out = array();
    while (count($dirlist)>0) {
      $dir = array_shift($dirlist);
      $dfp = @opendir($dir); if (!$dfp) { continue; }
      $dirslash = substr_count($dir, '/') + 1;
      $o = array();
      while ( ($pagefile = readdir($dfp)) !== false) {
        if ($pagefile[0] == '.') continue;
        if ($dirslash < $maxslash && is_dir("$dir/$pagefile"))
          { array_push($dirlist,"$dir/$pagefile"); continue; }
        if ($dirslash == $maxslash) $o[] = $this->PFD($pagefile);
      }
      closedir($dfp);
      StopWatch("PageStore::ls merge {$this->dirfmt}");
      $out = array_merge($out, MatchPageNames($o, $pats));
    }
    StopWatch("PageStore::ls end {$this->dirfmt}");
    return $out;
  }
  function recode($pagename, $a) {
    if (!$a) return false;
    global $Charset, $PageRecodeFunction, $DefaultPageCharset, $EnableOldCharset;
    if (function_exists($PageRecodeFunction)) return $PageRecodeFunction($a);
    if (IsEnabled($EnableOldCharset)) $a['=oldcharset'] = @$a['charset'];
    SDVA($DefaultPageCharset, array(''=>@$Charset)); # pre-2.2.31 RecentChanges
    if (@$DefaultPageCharset[$a['charset']]>'')  # wrong pre-2.2.30 encs. *-2, *-9, *-13
      $a['charset'] = $DefaultPageCharset[@$a['charset']];
    if (!$a['charset'] || $Charset==$a['charset']) return $a;
    $from = ($a['charset']=='ISO-8859-1') ? 'WINDOWS-1252' : $a['charset'];
    $to = ($Charset=='ISO-8859-1') ? 'WINDOWS-1252' : $Charset;
    if($from != $to) {
      foreach($a as $k=>$v) $a[$k] = $this->recodefn($v,$from,$to);
    }
    $a['charset'] = $Charset;
    return $a;
  }
}

function ReadPage($pagename, $since=0) {
  # read a page from the appropriate directories given by $WikiReadDirsFmt.
  global $WikiLibDirs,$Now;
  foreach ($WikiLibDirs as $dir) {
    $page = $dir->read($pagename, $since);
    if ($page) break;
  }
  if (@!$page) $page['ctime'] = $Now;
  if (@!$page['time']) $page['time'] = $Now;
  return $page;
}

function WritePage($pagename,$page) {
  global $WikiLibDirs,$WikiDir,$LastModFile;
  $WikiDir->iswrite = 1;
  for($i=0; $i<count($WikiLibDirs); $i++) {
    $wd = &$WikiLibDirs[$i];
    if ($wd->iswrite && $wd->exists($pagename)) break;
  }
  if ($i >= count($WikiLibDirs)) $wd = &$WikiDir;
  $wd->write($pagename,$page);
  if ($LastModFile && !@touch($LastModFile)) 
    { unlink($LastModFile); touch($LastModFile); fixperms($LastModFile); }
}

function PageExists($pagename) {
  ##  note:  $PageExistsCache might change or disappear someday
  global $WikiLibDirs, $PageExistsCache;
  if (!isset($PageExistsCache[$pagename])) {
    foreach((array)$WikiLibDirs as $dir)
      if ($PageExistsCache[$pagename] = $dir->exists($pagename)) break;
  }
  return $PageExistsCache[$pagename];
}

function ListPages($pat=NULL) {
  global $WikiLibDirs;
  foreach((array)$WikiLibDirs as $dir) 
    $out = array_unique(array_merge($dir->ls($pat),(array)@$out));
  return $out;
}

function RetrieveAuthPage($pagename, $level, $authprompt=true, $since=0) {
  global $AuthFunction;
  SDV($AuthFunction,'PmWikiAuth');
  if (!function_exists($AuthFunction)) return ReadPage($pagename, $since);
  return $AuthFunction($pagename, $level, $authprompt, $since);
}

function Abort($msg, $info='') {
  # exit pmwiki with an abort message
  global $ScriptUrl, $Charset, $AbortFunction;
  if (@$AbortFunction) return $AbortFunction($msg, $info);
  if ($info) 
    $info = "<p class='vspace'><a target='_blank' rel='nofollow' href='http://www.pmwiki.org/pmwiki/info/$info'>$[More information]</a></p>";
  $msg = "<h3>$[PmWiki can't process your request]</h3>
    <p class='vspace'>$msg</p>
    <p class='vspace'>$[We are sorry for any inconvenience].</p>
    $info
    <p class='vspace'><a href='$ScriptUrl'>$[Return to] $ScriptUrl</a></p>";
  @header("Content-type: text/html; charset=$Charset");
  echo preg_replace_callback('/\\$\\[([^\\]]+)\\]/', "cb_expandxlang", $msg);
  exit;
}

function Redirect($pagename, $urlfmt='$PageUrl', $redirecturl=null) {
  # redirect the browser to $pagename
  global $EnableRedirect, $RedirectDelay, $EnableStopWatch;
  SDV($RedirectDelay, 0);
  clearstatcache();
  if (is_null($redirecturl)) $redirecturl = FmtPageName($urlfmt,$pagename);
  if (IsEnabled($EnableRedirect,1) && 
      (!isset($_REQUEST['redirect']) || $_REQUEST['redirect'])) {
    header("Location: $redirecturl");
    header("Content-type: text/html");
    echo "<html><head>
      <meta http-equiv='Refresh' Content='$RedirectDelay; URL=$redirecturl' />
     <title>Redirect</title></head><body></body></html>";
     exit;
  }
  echo "<a href='$redirecturl'>Redirect to $redirecturl</a>";
  if (@$EnableStopWatch && function_exists('StopWatchHTML'))
    StopWatchHTML($pagename, 1);
  exit;
}

function PrintFmt($pagename,$fmt) {
  global $HTTPHeaders,$FmtV;
  if (is_array($fmt)) 
    { foreach($fmt as $f) PrintFmt($pagename,$f); return; }
  if ($fmt == 'headers:') {
    foreach($HTTPHeaders as $h) (@$sent++) ? @header($h) : header($h);
    return;
  }
  $x = FmtPageName($fmt,$pagename);
  if (strncmp($fmt, 'function:', 9) == 0 &&
      preg_match('/^function:(\S+)\s*(.*)$/s', $x, $match) &&
      function_exists($match[1]))
    { $match[1]($pagename,$match[2]); return; }
  if (strncmp($fmt, 'file:', 5) == 0 && preg_match("/^file:(.+)/s",$x,$match)) {
    $filelist = preg_split('/[\\s]+/',$match[1],-1,PREG_SPLIT_NO_EMPTY);
    foreach($filelist as $f) {
      if (file_exists($f)) { include($f); return; }
    }
    return;
  }
  if (substr($x, 0, 7) == 'markup:')
    { print MarkupToHTML($pagename, substr($x, 7)); return; }
  if (substr($x, 0, 5) == 'wiki:')
    { PrintWikiPage($pagename, substr($x, 5), 'read'); return; }
  if (substr($x, 0, 5) == 'page:')
    { PrintWikiPage($pagename, substr($x, 5), ''); return; }
  echo $x;
}

function PrintWikiPage($pagename, $wikilist=NULL, $auth='read') {
  if (is_null($wikilist)) $wikilist=$pagename;
  $pagelist = preg_split('/\s+/',$wikilist,-1,PREG_SPLIT_NO_EMPTY);
  foreach($pagelist as $p) {
    if (PageExists($p)) {
      $page = ($auth) ? RetrieveAuthPage($p, $auth, false, READPAGE_CURRENT)
              : ReadPage($p, READPAGE_CURRENT);
      if ($page['text']) 
        echo MarkupToHTML($pagename,Qualify($p, $page['text']));
      return;
    }
  }
}

function Keep($x, $pool=NULL) {
  if(is_array($x)) $x = $x[0]; # used in many callbacks
  # Keep preserves a string from being processed by wiki markups
  global $BlockPattern, $KeepToken, $KPV, $KPCount;
  $x = preg_replace_callback("/$KeepToken(\\d.*?)$KeepToken/", 'cb_expandkpv', $x);
  if (is_null($pool) && preg_match("!</?($BlockPattern)\\b!", $x)) $pool = 'B';
  $KPCount++; $KPV[$KPCount.$pool]=$x;
  return $KeepToken.$KPCount.$pool.$KeepToken;
}


##  MarkupEscape examines markup source and escapes any [@...@]
##  and [=...=] sequences using Keep().  MarkupRestore undoes the
##  effect of any MarkupEscape().
function MarkupEscape($text) {
  global $EscapePattern;
  SDV($EscapePattern, '\\[([=@]).*?\\1\\]');
  return preg_replace_callback("/$EscapePattern/s", "Keep", $text);
}
function MarkupRestore($text) {
  global $KeepToken, $KPV;
  return preg_replace_callback("/$KeepToken(\\d.*?)$KeepToken/", 'cb_expandkpv', $text);
}


##  Qualify() applies $QualifyPatterns to convert relative links
##  and references into absolute equivalents.
function Qualify($pagename, $text) {
  global $QualifyPatterns, $KeepToken, $KPV, $tmp_qualify;
  if (!@$QualifyPatterns) return $text;
  $text = MarkupEscape($text);
  $group = $tmp_qualify['group'] = PageVar($pagename, '$Group');
  $name  = $tmp_qualify['name']  = PageVar($pagename, '$Name');
  $tmp_qualify['pagename'] = $pagename;
  $text = PPRA((array)$QualifyPatterns, $text);
  return MarkupRestore($text);
}


function CondText($pagename,$condspec,$condtext) {
  global $Conditions;
  if (!preg_match("/^(\\S+)\\s*(!?)\\s*(\\S+)?\\s*(.*?)\\s*$/",
    $condspec,$match)) return '';
  @list($condstr,$condtype,$not,$condname,$condparm) = $match;
  if (isset($Conditions[$condname])) {
    $tf = @eval("return (".$Conditions[$condname].");");
    if (!$tf xor $not) $condtext='';
  }
  return $condtext;
}


##  TextSection extracts a section of text delimited by page anchors.
##  The $sections parameter can have the form
##    #abc           - [[#abc]] to next anchor
##    #abc#def       - [[#abc]] up to [[#def]]
##    #abc#, #abc..  - [[#abc]] to end of text
##    ##abc, #..#abc - beginning of text to [[#abc]]
##  Returns the text unchanged if no sections are requested,
##  or false if a requested beginning anchor isn't in the text.
function TextSection($text, $sections, $args = NULL) {
  $args = (array)$args;
  $npat = '[[:alpha:]][-\\w.]*';
  if (!preg_match("/#($npat)?(\\.\\.)?(#($npat)?)?/", $sections, $match))
    return $text;
  @list($x, $aa, $dots, $b, $bb) = $match;
  if (!$dots && !$b) $bb = $npat;
  if ($aa) {
    $aa = preg_replace('/\\.\\.$/', '', $aa);
    $pos = strpos($text, "[[#$aa]]");  if ($pos === false) return false;
    if (@$args['anchors']) 
      while ($pos > 0 && $text[$pos-1] != "\n") $pos--;
    else $pos += strlen("[[#$aa]]");
    $text = substr($text, $pos);
  }
  if ($bb)
    $text = preg_replace("/(\n)[^\n]*\\[\\[#$bb\\]\\].*$/s", '$1', $text, 1);
  return $text;
}


##  RetrieveAuthSection extracts a section of text from a page.
##  If $pagesection starts with anything other than '#', it identifies
##  the page to extract text from.  Otherwise RetrieveAuthSection looks
##  in the pages given by $list, or in $pagename if $list is not specified.
##  The selected page is placed in the global $RASPageName variable.
##  The caller is responsible for calling Qualify() as needed.
function RetrieveAuthSection($pagename, $pagesection, $list=NULL, $auth='read') {
  global $RASPageName, $PCache;
  if ($pagesection[0] != '#')
    $list = array(MakePageName($pagename, $pagesection));
  else if (is_null($list)) $list = array($pagename);
  foreach((array)$list as $t) {
    $t = FmtPageName($t, $pagename);
    if (!PageExists($t)) continue;
    $tpage = RetrieveAuthPage($t, $auth, false, READPAGE_CURRENT);
    if (!$tpage) continue;
    $text = TextSection(IsEnabled($PCache[$t]['=preview'],$tpage['text']),$pagesection);
    if ($text !== false) { $RASPageName = $t; return $text; }
  }
  $RASPageName = '';
  return false;
}

function IncludeText($pagename, $inclspec) {
  global $MaxIncludes, $IncludeOpt, $InclCount, $PCache;
  SDV($MaxIncludes,50);
  SDVA($IncludeOpt, array('self'=>1));
  if (@$InclCount[$pagename]++>=$MaxIncludes) return Keep($inclspec);
  $args = array_merge($IncludeOpt, ParseArgs($inclspec));
  while (count($args['#'])>0) {
    $k = array_shift($args['#']); $v = array_shift($args['#']);
    if ($k=='') {
      if ($v[0] != '#') {
        if (isset($itext)) continue;
        $iname = MakePageName($pagename, $v);
        if (!$args['self'] && $iname == $pagename) continue;
        $ipage = RetrieveAuthPage($iname, 'read', false, READPAGE_CURRENT);
        $itext = IsEnabled($PCache[$iname]['=preview'], @$ipage['text']);
      }
      $itext = TextSection(@$itext, $v, array('anchors' => 1));
      continue;
    }
    if (preg_match('/^(?:line|para)s?$/', $k)) {
      preg_match('/^(\\d*)(\\.\\.(\\d*))?$/', $v, $match);
      @list($x, $a, $dots, $b) = $match;
      $upat = ($k[0] == 'p') ? ".*?(\n\\s*\n|$)" : "[^\n]*(?:\n|$)";
      if (!$dots) { $b=$a; $a=0; }
      if ($a>0) $a--;
      $itext=preg_replace("/^(($upat){0,$b}).*$/s",'$1',$itext,1);
      $itext=preg_replace("/^($upat){0,$a}/s",'',$itext,1);
      continue;
    }
  }
  $basepage = isset($args['basepage']) 
              ? MakePageName($pagename, $args['basepage'])
              : @$iname;
  if ($basepage) $itext = Qualify(@$basepage, @$itext);
  return FmtTemplateVars(PVSE(@$itext), $args);
}


function RedirectMarkup($pagename, $opt) {
  $k = Keep("(:redirect $opt:)");
  global $MarkupFrame, $EnableRedirectQuiet;
  if (!@$MarkupFrame[0]['redirect']) return $k;
  $opt = ParseArgs($opt);
  $to = @$opt['to']; if (!$to) $to = @$opt[''][0];
  if (!$to) return $k;
  if (preg_match('/^([^#]+)(#[A-Za-z][-\\w]*)$/', $to, $match)) 
    { $to = $match[1]; $anchor = @$match[2]; }
  $to = MakePageName($pagename, $to);
  if (!PageExists($to)) return $k;
  if ($to == $pagename) return '';
  if (@$opt['from'] 
      && !MatchPageNames($pagename, FixGlob($opt['from'], '$1*.$2')))
    return '';
  if (preg_match('/^30[1237]$/', @$opt['status'])) 
     header("HTTP/1.1 {$opt['status']}");
  Redirect($to, "{\$PageUrl}"
    . (IsEnabled($EnableRedirectQuiet, 0) && IsEnabled($opt['quiet'], 0)
      ? '' : "?from=$pagename")
    . $anchor);
  exit();
}
   

function Block($b) {
  global $BlockMarkups,$HTMLVSpace,$HTMLPNewline,$MarkupFrame;
  $mf = &$MarkupFrame[0]; $cs = &$mf['cs']; $vspaces = &$mf['vs'];
  $out = '';
  if ($b == 'vspace') {
    $vspaces .= "\n";
    while (count($cs)>0 && @end($cs)!='pre' && @$BlockMarkups[@end($cs)][3]==0) 
      { $c = array_pop($cs); $out .= $BlockMarkups[$c][2]; }
    return $out;
  }
  @list($code, $depth, $icol) = explode(',', $b);
  if (!$code) $depth = 1;
  if (!is_numeric($depth)) $depth = strlen($depth); # PHP8
  if (!is_numeric($icol)) $icol = strlen($icol); # PHP8
  if ($depth > 0) $depth += @$mf['idep'];
  if ($icol > 0) $mf['is'][$depth] = $icol + @$mf['icol'];
  @$mf['idep'] = @$mf['icol'] = 0;
  while (count($cs)>$depth) 
    { $c = array_pop($cs); $out .= @$BlockMarkups[$c][2]; }
  if (!$code) {
    if (@end($cs) == 'p') { $out .= $HTMLPNewline; $code = 'p'; }
    else if ($depth < 2) { $code = 'p'; $mf['is'][$depth] = 0; }
    else { $out .= $HTMLPNewline; $code = 'block'; }
  }
  if ($depth>0 && $depth==count($cs) && $cs[$depth-1]!=$code)
    { $c = array_pop($cs); $out .= $BlockMarkups[$c][2]; }
  while (count($cs)>0 && @end($cs)!=$code &&
      @$BlockMarkups[@end($cs)][3]==0)
    { $c = array_pop($cs); $out .= $BlockMarkups[$c][2]; }
  if ($vspaces) { 
    $out .= (@end($cs) == 'pre') ? $vspaces : $HTMLVSpace; 
    $vspaces=''; 
  }
  if ($depth==0) { return $out; }
  if ($depth==count($cs)) { return $out.$BlockMarkups[$code][1]; }
  while (count($cs)<$depth-1) { 
    array_push($cs, 'dl'); $mf['is'][count($cs)] = 0;
    $out .= $BlockMarkups['dl'][0].'<dd>'; 
  }
  if (count($cs)<$depth) {
    array_push($cs,$code);
    $out .= $BlockMarkups[$code][0];
  }
  return $out;
}


function MarkupClose($key = '') {
  global $MarkupFrame;
  $cf = & $MarkupFrame[0]['closeall'];
  $out = '';
  if ($key == '' || isset($cf[$key])) {
    $k = array_keys((array)$cf);
    while ($k) { 
      $x = array_pop($k); $out .= $cf[$x]; unset($cf[$x]);
      if ($x == $key) break;
    }
  }
  return $out;
}


function FormatTableRow($x, $sep = '\\|\\|') {
  global $TableCellAttrFmt, $TableCellAlignFmt, $TableRowAttrFmt,
    $TableRowIndexMax, $MarkupFrame, $FmtV, $EnableSimpleTableRowspan;
  static $rowcount;
  SDV($TableCellAlignFmt, " align='%s'");

  if (IsEnabled($EnableSimpleTableRowspan, 0)) {
    $x = preg_replace("/\\|\\|__+(?=\\|\\|)/", '||', $x);
    $x = preg_replace("/\\|\\|\\^\\^+(?=\\|\\|)/", '', $x);
  }
  $x = preg_replace("/$sep\\s*$/",'',$x);
  $td = preg_split("/$sep/", $x); $y = '';
  for($i=0;$i<count($td);$i++) {
    if ($td[$i]=='') continue;
    $FmtV['$TableCellCount'] = $i;
    $attr = FmtPageName(@$TableCellAttrFmt, '');
    if (IsEnabled($EnableSimpleTableRowspan, 0)) {
      if (preg_match('/(\\+\\++)\\s*$/', $td[$i], $rspn)) {
        $td[$i] = preg_replace('/\\+\\++(\\s*)$/', '$1', $td[$i]);
        $attr .= " rowspan='".strlen($rspn[1])."'";
      }
    }
    $td[$i] = preg_replace('/^(!?)\\s+$/', '$1&nbsp;', $td[$i]);
    if (preg_match('/^!(.*?)!$/',$td[$i],$match))
      { $td[$i]=$match[1]; $t='caption'; $attr=''; }
    elseif (preg_match('/^!(.*)$/',$td[$i],$match)) 
      { $td[$i]=$match[1]; $t='th'; }
    else $t='td';
    if (preg_match('/^\\s.*\\s$/',$td[$i])) {
      if ($t!='caption') $attr .= sprintf($TableCellAlignFmt, 'center');
    }
    elseif (preg_match('/^\\s/',$td[$i])) { $attr .= sprintf($TableCellAlignFmt, 'right'); }
    elseif (preg_match('/\\s$/',$td[$i])) { $attr .= sprintf($TableCellAlignFmt, 'left'); }
    for ($colspan=1;$i+$colspan<count($td);$colspan++) 
      if ($td[$colspan+$i]!='') break;
    if ($colspan>1) { $attr .= " colspan='$colspan'"; }
    $y .= "<$t $attr>".trim($td[$i])."</$t>";
  }
  if ($t=='caption') return "<:table,1>$y";
  if (@$MarkupFrame[0]['cs'][0] != 'table') $rowcount = 0; else $rowcount++;
  $FmtV['$TableRowCount'] = $rowcount + 1;
  $FmtV['$TableRowIndex'] = ($rowcount % $TableRowIndexMax) + 1;
  $trattr = FmtPageName(@$TableRowAttrFmt, '');
  return "<:table,1><tr $trattr>$y</tr>";
}

function LinkIMap($pagename,$imap,$path,$alt,$txt,$fmt=NULL) {
  global $FmtV, $IMap, $IMapLinkFmt, $UrlLinkFmt, $IMapLocalPath, $ScriptUrl, $AddLinkCSS;
  SDVA($IMapLocalPath, array('Path:'=>1));
  if (@$IMapLocalPath[$imap]) {
    $path = preg_replace('/^(\\w+):/', "$1%3a", $path); # PITS:01260
  }
  $FmtV['$LinkUrl'] = PUE(str_replace('$1',$path,$IMap[$imap]));
  $FmtV['$LinkText'] = $txt;
  $FmtV['$LinkAlt'] = Keep(str_replace(array('"',"'"),array('&#34;','&#39;'),$alt));
  if (!$fmt) 
    $fmt = (isset($IMapLinkFmt[$imap])) ? $IMapLinkFmt[$imap] : $UrlLinkFmt;
  if(IsEnabled($AddLinkCSS['samedomain'])) {
    $parsed_url = parse_url($FmtV['$LinkUrl']);
    $parsed_wiki = parse_url($ScriptUrl);
    if(! @$parsed_url['host'] || $parsed_url['host'] == $parsed_wiki['host']) {
      $fmt = preg_replace('/(<a[^>]*class=["\'])/', "$1{$AddLinkCSS['samedomain']} ", $fmt);
    }
  }
  # remove unused title attributes
  if(!$alt) $fmt = preg_replace('/\\stitle=([\'"])\\$LinkAlt\\1/', '', $fmt);
  return str_replace(array_keys($FmtV),array_values($FmtV),$fmt);
}

## These 2 functions hide e-mail addresses from spam bot harvesters
## recover them for most users with a javascript utility,
## while keeping them readable for users with JS disabled.
## Based on Cookbook:DeObMail by Petko Yotov
## To enable, set $LinkFunctions['mailto:'] = 'ObfuscateLinkIMap';
function ObfuscateLinkIMap($pagename,$imap,$path,$title,$txt,$fmt=NULL) {
  global $FmtV, $IMap, $IMapLinkFmt;
  SDVA($IMapLinkFmt, array('obfuscate-mailto:' =>
    "<span class='_pmXmail' title=\"\$LinkAlt\"><span class='_t'>\$LinkText</span><span class='_m'>\$LinkUrl</span></span>"));
  $FmtV['$LinkUrl'] = cb_obfuscate_mail(str_replace('$1',$path,$IMap[$imap]));
  $FmtV['$LinkText'] = cb_obfuscate_mail(preg_replace('/^mailto:/i', '', $txt));
  if($FmtV['$LinkText'] == preg_replace('/^mailto:/i', '', $FmtV['$LinkUrl'])) $FmtV['$LinkUrl'] = '';
  else $FmtV['$LinkUrl'] = " -&gt; ".$FmtV['$LinkUrl'];
  $FmtV['$LinkAlt'] = str_replace(array('"',"'"),array('&#34;','&#39;'),cb_obfuscate_mail($title, 0));
  return str_replace(array_keys($FmtV),array_values($FmtV), $IMapLinkFmt['obfuscate-mailto:']);
}

function cb_obfuscate_mail($x, $wrap=1) {
  $classes = array('.' => '_d', '@' => '_a');
  $texts = array( '.' => XL(' [period] '), '@' => XL(' [snail] '));
  foreach($classes as $k=>$v)
    $x = preg_replace("/(\\w)".preg_quote($k)."(\\w)/",
    ($wrap?
      "$1<span class='$v'>{$texts[$k]}</span>$2"
      : "$1{$texts[$k]}$2")
      , $x);
  return $x;
}

function LinkPage($pagename,$imap,$path,$alt,$txt,$fmt=NULL) {
  global $QueryFragPattern, $LinkPageExistsFmt, $LinkPageSelfFmt,
    $LinkPageCreateSpaceFmt, $LinkPageCreateFmt, $LinkTargets,
    $EnableLinkPageRelative, $EnableLinkPlusTitlespaced, $AddLinkCSS;
  $alt = str_replace(array('"',"'"),array('&#34;','&#39;'),$alt);
  $path = preg_replace('/(#[-.:\\w]*)#.*$/', '$1', $path); # PITS:01388
  if (is_array($txt)) { # PITS:01392
    $suffix = $txt[1];
    $txt = $txt[0];
  }
  if (!$fmt && @$path[0] == '#') {
    $path = preg_replace("/[^-.:\\w]/", '', $path);
    if (trim($txt) == '+') $txt = PageVar($pagename, '$Title') . @$suffix;
    if ($alt) $alt = " title='$alt'";
    return ($path) ? "<a href='#$path'$alt>".str_replace("$", "&#036;", $txt)."</a>" : '';
  }
  if (!preg_match("/^\\s*([^#?]+)($QueryFragPattern)?$/",$path,$match))
    return '';
  $tgtname = MakePageName($pagename, $match[1]); 
  if (!$tgtname) return '';
  $qf = @$match[2];
  @$LinkTargets[$tgtname]++;
  if (!$fmt) {
    if (!PageExists($tgtname) && !preg_match('/[&?]action=/', $qf))
      $fmt = preg_match('/\\s/', $txt) 
             ? $LinkPageCreateSpaceFmt : $LinkPageCreateFmt;
    else
      $fmt = ($tgtname == $pagename && $qf == '') 
             ? $LinkPageSelfFmt : $LinkPageExistsFmt;
  }
  $url = PageVar($tgtname, '$PageUrl');
  if (trim($txt) == '+') $txt = PageVar($tgtname, 
    IsEnabled($EnableLinkPlusTitlespaced, 0) ? '$Titlespaced' : '$Title') . @$suffix;
  $txt = str_replace("$", "&#036;", $txt);
  if (@$EnableLinkPageRelative)
    $url = preg_replace('!^[a-z]+://[^/]*!i', '', $url);
  # remove unused title attributes
  if(!$alt) $fmt = preg_replace('/\\stitle=([\'"])\\$LinkAlt\\1/', '', $fmt);
  $fmt = str_replace(array('$LinkUrl', '$LinkText', '$LinkAlt'),
                     array($url.PUE($qf), $txt, Keep($alt)), $fmt);
  if(IsEnabled($AddLinkCSS['othergroup'])) {
    list($cgroup, ) = explode('.', $pagename);
    list($tgroup, ) = explode('.', $tgtname);
    if($cgroup != $tgroup) 
      $fmt = preg_replace('/(<a[^>]*class=["\'])/', "$1{$AddLinkCSS['othergroup']} ", $fmt);
  }
  return FmtPageName($fmt,$tgtname);
}

function MakeLink($pagename,$tgt,$txt=NULL,$suffix=NULL,$fmt=NULL) {
  global $LinkPattern,$LinkFunctions,$UrlExcludeChars,$ImgExtPattern,$ImgTagFmt,
    $LinkTitleFunction;
  if(preg_match("/^(.*)(?:\"(.*)\")\\s*$/",$tgt,$x)) list(,$tgt,$title) = $x;
  $t = preg_replace('/[()]/','',trim($tgt));
  $t = preg_replace('/<[^>]*>/','',$t);
  $t = trim(MarkupRestore($t));
  $txtr = trim(MarkupRestore($txt));
  
  preg_match("/^($LinkPattern)?(.+)$/",$t,$m);
  if (!@$m[1]) $m[1]='<:page>';
  if (preg_match("/(($LinkPattern)([^$UrlExcludeChars]+$ImgExtPattern))(\"(.*)\")?$/",$txtr,$tm)) 
    $txt = $LinkFunctions[$tm[2]]($pagename,$tm[2],$tm[3],@$tm[5],
      $tm[1],$ImgTagFmt);
  else {
    if (is_null($txt)) {
      $txt = preg_replace('/\\([^)]*\\)/','',$tgt);
      if ($m[1]=='<:page>') {
        $txt = preg_replace('!/\\s*$!', '', $txt);
        $txt = preg_replace('!^.*[^<]/!', '', $txt);
      }
    }
    if ($m[1]=='<:page>' && trim($txt) == '+' && $suffix>'') { # PITS:01392
      $txt = array(trim($txt), $suffix);
    }
    else $txt .= $suffix;
  }
  if (@$LinkTitleFunction) $title = $LinkTitleFunction($pagename,$m,$txt);
  else $title = PHSC(MarkupRestore(@$title), ENT_QUOTES);
  $out = $LinkFunctions[$m[1]]($pagename,$m[1],@$m[2],@$title,$txt,$fmt);
  return preg_replace('/(<[^>]+)\\stitle=(""|\'\')/', '$1', $out);
}

function Markup($id, $when, $pat=NULL, $rep=NULL, $tracelev=0) {
  global $MarkupTable, $EnableMarkupDiag;
  unset($GLOBALS['MarkupRules']);
  if (preg_match('/^([<>])?(.+)$/', $when, $m)) {
    $MarkupTable[$id]['cmd'] = $when;
    $MarkupTable[$m[2]]['dep'][$id] = $m[1];
    if (!$m[1]) $m[1] = '=';
    if (@$MarkupTable[$m[2]]['seq']) {
      $MarkupTable[$id]['seq'] = $MarkupTable[$m[2]]['seq'].$m[1];
      foreach((array)@$MarkupTable[$id]['dep'] as $i=>$m)
        Markup($i,"$m$id");
      unset($GLOBALS['MarkupTable'][$id]['dep']);
    }
  }
  if ($pat && !isset($MarkupTable[$id]['pat'])) {
    $oldpat = preg_match('!(^/.+/[^/]*)e([^/]*)$!', $pat, $mm);
    if($oldpat && PHP_VERSION_ID >= 50500) {
      # disable old markup for recent PHP versions
      $rep = 'ObsoleteMarkup';
      $pat = $mm[1].$mm[2];
    }    
    $MarkupTable[$id]['pat'] = $pat;
    $MarkupTable[$id]['rep'] = $rep;
    
    if (IsEnabled($EnableMarkupDiag, 0) || $oldpat) {
      $exmark = $oldpat ? '!' : ' ';
      if (function_exists('debug_backtrace')) {
        $dbg = debug_backtrace();
        $dbginfo = $dbg[$tracelev];
        $MarkupTable[$id]['dbg'] = "$exmark file: {$dbginfo['file']}, "
          . "line: {$dbginfo['line']}, pat: {$dbginfo['args'][2]}";
      }
      else 
        $MarkupTable[$id]['dbg'] = "$exmark id: '$id', pat: '$pat'";
    }
  }
}

function Markup_e($id, $when, $pat, $rep, $template = 'markup_e') {
  if (!is_callable($rep)) {
    if(function_exists('create_function'))
      $rep = PCCF($rep, $template);
    else $rep = 'ObsoleteMarkup';
  }
  Markup($id, $when, $pat, $rep, 1);
}

function ObsoleteMarkup($m) {
  extract($GLOBALS['MarkupToHTML']);
  $id = PHSC($markupid, ENT_QUOTES);
  $txt = PHSC($m[0], ENT_QUOTES);
  return Keep("<code title='Markup rule &quot;$id&quot; is obsolete and has been disabled. See pmwiki.org/Troubleshooting' 
    class='obsolete-markup frame'>⚠️$txt</code>");
}

function DisableMarkup() {
  global $MarkupTable;
  $idlist = func_get_args();
  unset($GLOBALS['MarkupRules']);
  while (count($idlist)>0) {
    $id = array_shift($idlist);
    if (is_array($id)) { $idlist = array_merge($idlist, $id); continue; }
    $MarkupTable[$id] = array('cmd' => 'none', 'pat'=>'');
  }
}

function mpcmp($a,$b) { return @strcmp($a['seq'].'=',$b['seq'].'='); }
function BuildMarkupRules() {
  global $MarkupTable,$MarkupRules,$LinkPattern;
  if (!$MarkupRules) {
    uasort($MarkupTable,'mpcmp');
    foreach($MarkupTable as $id=>$m) 
      if (@$m['pat'] && @$m['seq']) {
        $MarkupRules[str_replace('\\L',$LinkPattern,$m['pat'])]
          = array($m['rep'], $id);
      }
  }
  return $MarkupRules;
}


function MarkupToHTML($pagename, $text, $opt = NULL) {
  # convert wiki markup text to HTML output
  global $MarkupRules, $MarkupFrame, $MarkupFrameBase, $WikiWordCount,
    $K0, $K1, $RedoMarkupLine, $MarkupToHTML;
  $MarkupToHTML['pagename'] = $pagename;

  StopWatch('MarkupToHTML begin');
  array_unshift($MarkupFrame, array_merge($MarkupFrameBase, (array)$opt));
  $MarkupFrame[0]['wwcount'] = $WikiWordCount;
  foreach((array)$text as $l) 
    $lines[] = $MarkupFrame[0]['escape'] ? PVSE($l) : $l;
  $lines[] = '(:closeall:)';
  $out = '';
  while (count($lines)>0) {
    $x = array_shift($lines);
    $RedoMarkupLine=0;
    $markrules = BuildMarkupRules();
    foreach($markrules as $p=>$r) {
      list($r, $id) = (array)$r;
      $MarkupToHTML['markupid'] = $id;
      if ($p[0] == '/') {
        if (is_callable($r)) $x = preg_replace_callback($p,$r,$x);
        else $x=preg_replace($p,$r,$x); # simple text OR called by old addon|skin|recipe needing update, see pmwiki.org/Troubleshooting
      }
      elseif (strstr($x,$p)!==false) $x=eval($r);
      if (isset($php_errormsg)) ### TODO: $php_errormsg removed since PHP 8
        { echo "ERROR: pat=$p $php_errormsg"; unset($php_errormsg); }
      if ($RedoMarkupLine) { $lines=array_merge((array)$x,$lines); continue 2; }
    }
    if ($x>'') $out .= "$x\n";
  }
  foreach((array)(@$MarkupFrame[0]['posteval']) as $v) eval($v);
  array_shift($MarkupFrame);
  StopWatch('MarkupToHTML end');
  return $out;
}
   
function HandleBrowse($pagename, $auth = 'read') {
  # handle display of a page
  global $DefaultPageTextFmt, $PageNotFoundHeaderFmt, $HTTPHeaders,
    $EnableHTMLCache, $NoHTMLCache, $PageCacheFile, $LastModTime, $IsHTMLCached,
    $FmtV, $HandleBrowseFmt, $PageStartFmt, $PageEndFmt, $PageRedirectFmt;
  $page = RetrieveAuthPage($pagename, $auth, true, READPAGE_CURRENT);
  if (!$page) Abort("?cannot read $pagename");
  PCache($pagename,$page);
  if (PageExists($pagename)) $text = @$page['text'];
  else {
    SDV($DefaultPageTextFmt,'(:include $[{$SiteGroup}.PageNotFound]:)');
    $text = FmtPageName($DefaultPageTextFmt, $pagename);
    SDV($PageNotFoundHeaderFmt, 'HTTP/1.1 404 Not Found');
    SDV($HTTPHeaders['status'], $PageNotFoundHeaderFmt);
  }
  $opt = array();
  SDV($PageRedirectFmt,"<p><i>($[redirected from] <a rel='nofollow' 
    href='{\$PageUrl}?action=edit'>{\$FullName}</a>)</i></p>\n");
  if (@!$_GET['from']) { $opt['redirect'] = 1; $PageRedirectFmt = ''; }
  else {
    $frompage = MakePageName($pagename, $_GET['from']);
    $PageRedirectFmt = (!$frompage) ? ''
      : FmtPageName($PageRedirectFmt, $frompage);
  }
  if (@$EnableHTMLCache && !$NoHTMLCache && $PageCacheFile && 
      @filemtime($PageCacheFile) > $LastModTime) {
    list($ctext) = unserialize(file_get_contents($PageCacheFile));
    $FmtV['$PageText'] = "<!--cached-->$ctext";
    $IsHTMLCached = 1;
    StopWatch("HandleBrowse: using cached copy");
  } else {
    $IsHTMLCached = 0;
    $text = '(:groupheader:)'.@$text.'(:groupfooter:)';
    $t1 = time();
    $FmtV['$PageText'] = MarkupToHTML($pagename, $text, $opt);
    if (@$EnableHTMLCache > 0 && !$NoHTMLCache && $PageCacheFile
        && (time() - $t1 + 1) >= $EnableHTMLCache) {
      $fp = @fopen("$PageCacheFile,new", "x");
      if ($fp) { 
        StopWatch("HandleBrowse: caching page");
        fwrite($fp, serialize(array($FmtV['$PageText']))); fclose($fp);
        rename("$PageCacheFile,new", $PageCacheFile);
      }
    }
  }
  SDV($HandleBrowseFmt,array(&$PageStartFmt, &$PageRedirectFmt, '$PageText',
    &$PageEndFmt));
  PrintFmt($pagename,$HandleBrowseFmt);
}


## UpdatePage goes through all of the steps needed to update a page,
## preserving page history, computing link targets, page titles, 
## and other page attributes.  It does this by calling each entry
## in $EditFunctions.  $pagename is the name of the page to be updated,
## $page is the old version of the page (used for page history),
## $new is the new version of the page to be saved, and $fnlist is
## an optional list of functions to use instead of $EditFunctions.
function UpdatePage(&$pagename, &$page, &$new, $fnlist = NULL) {
  global $EditFunctions, $IsPagePosted;
  StopWatch("UpdatePage: begin $pagename");
  if (is_null($fnlist)) $fnlist = $EditFunctions;
  $IsPagePosted = false;
  foreach((array)$fnlist as $fn) {
    StopWatch("UpdatePage: $fn ($pagename)");
    $fn($pagename, $page, $new);
  }
  StopWatch("UpdatePage: end $pagename");
  return $IsPagePosted;
}

# AutoCheckToken verifies if the posted content was sent
# from the website forms, to prevent CSRF
function AutoCheckToken() {
  # TODO: Work in progress (Jan 2021), releasing for 
  return true;
    
  global $EnablePost, $AutoCheckTokenActions, $EnablePmToken, 
    $FmtV, $action, $BlockMessageFmt, $MessagesFmt;
  
  # a quick way to disable tokens
  if(! IsEnabled($EnablePmToken, 1)) return true; 
  
  SDVA($AutoCheckTokenActions, array( # 1=POST, 2=GET, 0=disabled
    'edit' => 1, 
    'postattr' => 1,
    'postupload' => 1,
    'approvesites' => 2,
    'approveurls' => 2,
  ));
  $tname = $FmtV['$TokenName'];
  $x = @$AutoCheckTokenActions[$action];
  if (!$x) return true;
  elseif ($x==1) {
    if ( count($_POST) < 1 || pmtoken(''.@$_POST[$tname]) ) return true;
  }
  elseif($x==2 && pmtoken(''.@$_GET[$tname])) return true;
  
  $EnablePost = 0;
  $MessagesFmt[] = $BlockMessageFmt;
  $MessagesFmt[] = XL('Token invalid or missing.');
  return false;
}

# EditTemplate allows a site administrator to pre-populate new pages
# with the contents of another page.
function EditTemplate($pagename, &$page, &$new) {
  global $EditTemplatesFmt;
  if (@$new['text'] > '') return;
  if (@$_REQUEST['template'] && PageExists($_REQUEST['template'])) {
    $p = RetrieveAuthPage($_REQUEST['template'], 'read', false,
             READPAGE_CURRENT);
    if ($p['text'] > '') $new['text'] = $p['text']; 
    return;
  }
  foreach((array)$EditTemplatesFmt as $t) {
    $p = RetrieveAuthPage(FmtPageName($t,$pagename), 'read', false,
             READPAGE_CURRENT);
    if (@$p['text'] > '') { $new['text'] = $p['text']; return; }
  }
}

# RestorePage handles returning to the version of text as of
# the version given by $restore or $_REQUEST['restore'].
function RestorePage($pagename,&$page,&$new,$restore=NULL) {
  if (is_null($restore)) $restore=@$_REQUEST['restore'];
  if (!$restore) return;
  $t = $page['text'];
  $nl = (substr($t,-1)=="\n");
  $t = explode("\n",$t);
  if ($nl) array_pop($t);
  krsort($page); reset($page);
  foreach($page as $k=>$v) {
    if ($k<$restore) break;
    if (strncmp($k, 'diff:', 5) != 0) continue;
    foreach(explode("\n",$v) as $x) {
      if (preg_match('/^(\\d+)(,(\\d+))?([adc])(\\d+)/',$x,$match)) {
        $a1 = $a2 = $match[1];
        if ($match[3]) $a2=$match[3];
        $b1 = $match[5];
        if ($match[4]=='d') array_splice($t,$b1,$a2-$a1+1);
        if ($match[4]=='c') array_splice($t,$b1-1,$a2-$a1+1);
        continue;
      }
      if (strncmp($x,'< ',2) == 0) { $nlflag=true; continue; }
      if (preg_match('/^> (.*)$/',$x,$match)) {
        $nlflag=false;
        array_splice($t,$b1-1,0,$match[1]); $b1++;
      }
      if ($x=='\\ No newline at end of file') $nl=$nlflag;
    }
  }
  if ($nl) $t[]='';
  $new['text']=implode("\n",$t);
  $new['=preview'] = $new['text'];
  PCache($pagename, $new);
  return $new['text'];
}

## ReplaceOnSave performs text replacements on the text being posted.
## Patterns held in $ROEPatterns are replaced on every edit request,
## patterns held in $ROSPatterns are replaced only when the page
## is being posted (as signaled by $EnablePost).
function ReplaceOnSave($pagename,&$page,&$new) {
  global $EnablePost, $ROSPatterns, $ROEPatterns;
  $new['text'] = ProcessROESPatterns(@$new['text'], $ROEPatterns);
  if ($EnablePost) {
    $new['text'] = ProcessROESPatterns($new['text'], $ROSPatterns);
  }
  $new['=preview'] = $new['text'];
  PCache($pagename, $new);
}
function ProcessROESPatterns($text, $patterns) {
  global $EnableROSEscape;
  if (IsEnabled($EnableROSEscape, 0)) $text = MarkupEscape($text);
  $text = PPRA((array)@$patterns, $text);
  if (IsEnabled($EnableROSEscape, 0)) $text = MarkupRestore($text);
  return $text;
}

function SaveAttributes($pagename,&$page,&$new) {
  global $EnablePost, $LinkTargets, $SaveAttrPatterns, $PCache,
    $SaveProperties;
  if (!$EnablePost) return;
  $text = PPRA($SaveAttrPatterns, $new['text']);
  $LinkTargets = array();
  $new['=html'] = MarkupToHTML($pagename,$text);
  $new['targets'] = implode(',',array_keys((array)$LinkTargets));
  $p = & $PCache[$pagename];
  foreach((array)$SaveProperties as $k) {
    if (@$p["=p_$k"]) $new[$k] = $p["=p_$k"];
    else unset($new[$k]);
  }
  unset($new['excerpt']);
}

function PostPage($pagename, &$page, &$new) {
  global $DiffKeepDays, $DiffFunction, $DeleteKeyPattern, $EnablePost,
    $Now, $Charset, $Author, $WikiDir, $IsPagePosted, $DiffKeepNum;
  SDV($DiffKeepDays,3650);
  SDV($DiffKeepNum,20);
  SDV($DeleteKeyPattern,"^\\s*delete\\s*$");
  $IsPagePosted = false;
  if ($EnablePost) {
    $new['charset'] = $Charset; # kept for now, may be needed if custom PageStore
    $new['author'] = @$Author;
    $new["author:$Now"] = @$Author;
    $new["host:$Now"] = $_SERVER['REMOTE_ADDR'];
    $diffclass = preg_replace('/\\W/','',@$_POST['diffclass']);
    if ($page['time']>0 && function_exists(@$DiffFunction)) 
      $new["diff:$Now:{$page['time']}:$diffclass"] =
        $DiffFunction($new['text'],@$page['text']);
    $keepgmt = $Now-$DiffKeepDays * 86400;
    $keepnum = array(); 
    $keys = array_keys($new);
    foreach($keys as $k)
      if (preg_match("/^\\w+:(\\d+)/",$k,$match)) {
        $keepnum[$match[1]] = 1;
        if (count($keepnum)>$DiffKeepNum && $match[1]<$keepgmt) 
          unset($new[$k]);
      }
    if (preg_match("/$DeleteKeyPattern/",$new['text'])){
      if (@$new['passwdattr']>'' && !CondAuth($pagename, 'attr'))
        Abort('$[The page has an "attr" attribute and cannot be deleted.]');
      else  $WikiDir->delete($pagename);
    }
    else WritePage($pagename,$new);
    $IsPagePosted = true;
  }
}

function PostRecentChanges($pagename,$page,$new,$Fmt=null) {
  global $IsPagePosted, $RecentChangesFmt, $RCDelimPattern, $RCLinesMax,
    $EnableRCDiffBytes;
  if (!$IsPagePosted && $Fmt==null) return;
  if (is_null($Fmt)) $Fmt = $RecentChangesFmt;
  foreach($Fmt as $rcfmt=>$pgfmt) {
    $rcname = FmtPageName($rcfmt,$pagename);  if (!$rcname) continue;
    $pgtext = FmtPageName($pgfmt,$pagename);  if (!$pgtext) continue;
    if (@$seen[$rcname]++) continue;

    if (IsEnabled($EnableRCDiffBytes, 0)) {
      $pgtext = PPRA(array(
        '/\\(([+-])(\\d+)\\)(\\s*=\\]\\s*)$/'=>'$3%diffmarkup%{$1($1$2)$1}%%', 
        '/\\(\\+(0\\)\\+\\}%%)$/'=>'(&#177;$1'), $pgtext);
    }
    $rcpage = ReadPage($rcname);
    $rcelim = preg_quote(preg_replace("/$RCDelimPattern.*$/",' ',$pgtext),'/');
    $rcpage['text'] = preg_replace("/^.*$rcelim.*\n/m", '', @$rcpage['text']);
    if (!preg_match("/$RCDelimPattern/",$rcpage['text'])) 
      $rcpage['text'] .= "$pgtext\n";
    else
      $rcpage['text'] = preg_replace("/([^\n]*$RCDelimPattern.*\n)/",
        str_replace("$", "\\$", $pgtext) . "\n$1", $rcpage['text'], 1);
    if (@$RCLinesMax > 0) 
      $rcpage['text'] = implode("\n", array_slice(
          explode("\n", $rcpage['text'], $RCLinesMax + 1), 0, $RCLinesMax));
    WritePage($rcname, $rcpage);
  }
}

function AutoCreateTargets($pagename, &$page, &$new) {
  global $IsPagePosted, $AutoCreate, $LinkTargets;
  if (!$IsPagePosted) return;
  foreach((array)@$AutoCreate as $pat => $init) {
    if (is_null($init)) continue;
    foreach(preg_grep($pat, array_keys((array)@$LinkTargets)) as $aname) {
      if (PageExists($aname)) continue;
      $x = RetrieveAuthPage($aname, 'edit', false, READPAGE_CURRENT);
      if (!$x) continue;
      WritePage($aname, $init);
    }
  }
}

function PreviewPage($pagename,&$page,&$new) {
  global $IsPageSaved, $FmtV, $ROSPatterns;
  if (@$_REQUEST['preview']) {
    $text = ProcessROESPatterns($new['text'], $ROSPatterns);
    $text = '(:groupheader:)'.$text.'(:groupfooter:)';
    $FmtV['$PreviewText'] = MarkupToHTML($pagename,$text);
  }
}

function HandleEdit($pagename, $auth = 'edit') {
  global $IsPagePosted, $EditFields, $ChangeSummary, $EditFunctions,
    $EnablePost, $FmtV, $Now, $EditRedirectFmt, $EnableRCDiffBytes, 
    $PageEditForm, $HandleEditFmt, $PageStartFmt, $PageEditFmt, $PageEndFmt;
  SDV($EditRedirectFmt, '$FullName');
  if (@$_POST['cancel']) 
    { Redirect(FmtPageName($EditRedirectFmt, $pagename)); return; }
  Lock(2);
  $page = RetrieveAuthPage($pagename, $auth, true);
  if (!$page) Abort("?cannot edit $pagename"); 
  $new = $page;
  foreach((array)$EditFields as $k) 
    if (isset($_POST[$k])) $new[$k]=str_replace("\r",'',stripmagic($_POST[$k]));
    
  if (IsEnabled($EnableRCDiffBytes, 0) && isset($new['text'])) {
    $bytes = strlen($new['text']) - strlen(@$page['text']);
    if ($bytes>=0) $bytes = "+$bytes";
    $ChangeSummary = rtrim($ChangeSummary) . " ($bytes)";
  }
  $new['csum'] = $ChangeSummary;
  if ($ChangeSummary) $new["csum:$Now"] = $ChangeSummary;
  $EnablePost &= (bool)preg_grep('/^post/', array_keys(@$_POST));
  $new['=preview'] = @$new['text'];
  PCache($pagename, $new);
  UpdatePage($pagename, $page, $new);
  Lock(0);
  if ($IsPagePosted && !@$_POST['postedit']) 
    { Redirect(FmtPageName($EditRedirectFmt, $pagename)); return; }
  $FmtV['$DiffClassMinor'] = 
    (@$_POST['diffclass']=='minor') ?  "checked='checked'" : '';
  $FmtV['$EditText'] = 
    str_replace('$','&#036;',PHSC(@$new['text'],ENT_NOQUOTES));
  $FmtV['$EditBaseTime'] = $Now;
  $FmtV['$TokenValue'] = pmtoken();
  if (@$PageEditForm) {
    $efpage = FmtPageName($PageEditForm, $pagename);
    $form = RetrieveAuthPage($efpage, 'read', false, READPAGE_CURRENT);
    if (!$form || !@$form['text']) 
      Abort("?unable to retrieve edit form $efpage", 'editform');
    $FmtV['$EditForm'] = MarkupToHTML($pagename, $form['text']);
  }
  SDV($PageEditFmt, "<div id='wikiedit'>
    <h2 class='wikiaction'>$[Editing {\$FullName}]</h2>
    <form method='post' rel='nofollow' action='\$PageUrl?action=edit'>
    <input type='hidden' name='action' value='edit' />
    <input type='hidden' name='n' value='\$FullName' />
    <input type='hidden' name='basetime' value='\$EditBaseTime' />
    <input type='hidden' name='\$TokenName' value='\$TokenValue' />
    \$EditMessageFmt
    <textarea id='text' name='text' rows='25' cols='60'
      onkeydown='if (event.keyCode==27) event.returnValue=false;'
      >\$EditText</textarea><br />
    <input type='submit' name='post' value=' $[Save] ' />");
  SDV($HandleEditFmt, array(&$PageStartFmt, &$PageEditFmt, &$PageEndFmt));
  PrintFmt($pagename, $HandleEditFmt);
}

function HandleSource($pagename, $auth = 'read') {
  global $HTTPHeaders;
  $page = RetrieveAuthPage($pagename, $auth, true, READPAGE_CURRENT);
  if (!$page) Abort("?cannot source $pagename");
  foreach ($HTTPHeaders as $h) {
    $h = preg_replace('!^Content-type:\\s+text/html!i',
             'Content-type: text/plain', $h);
    header($h);
  }
  echo @$page['text'];
}

## PmWikiAuth provides password-protection of pages using PHP sessions.
## It is normally called from RetrieveAuthPage.  Since RetrieveAuthPage
## can be called a lot within a single page execution (i.e., for every
## page accessed), we cache the results of site passwords and 
## GroupAttribute pages to be able to speed up subsequent calls.
function PmWikiAuth($pagename, $level, $authprompt=true, $since=0) {
  global $DefaultPasswords, $GroupAttributesFmt, $AllowPassword,
    $AuthCascade, $FmtV, $AuthPromptFmt, $PageStartFmt, $PageEndFmt, 
    $AuthId, $AuthList, $NoHTMLCache;
  static $acache;
  SDV($GroupAttributesFmt,'$Group/GroupAttributes');
  SDV($AllowPassword,'nopass');
  $page = ReadPage($pagename, $since);
  if (!$page) { return false; }
  if (!isset($acache)) 
    SessionAuth($pagename, (@$_POST['authpw']) 
                           ? array('authpw' => array($_POST['authpw'] => 1))
                           : '');
  if (@$AuthId) {
    $AuthList["id:$AuthId"] = 1;
    $AuthList["id:-$AuthId"] = -1;
    $AuthList["id:*"] = 1;
  }
  ## To allow @_site_edit in GroupAttributes, we cache it first
  if (!isset($acache['@site'])) {
    foreach($DefaultPasswords as $k => $v) {
      $x = array(2, array(), '');
      $acache['@site'][$k] = IsAuthorized($v, 'site', $x);
      $AuthList["@_site_$k"] = $acache['@site'][$k][0] ? 1 : 0;
    }
  }
  $gn = FmtPageName($GroupAttributesFmt, $pagename);
  if (!isset($acache[$gn])) {
    $gp = ReadPage($gn, READPAGE_CURRENT);
    foreach($DefaultPasswords as $k => $v) {
      $acache[$gn][$k] = IsAuthorized(@$gp["passwd$k"], 'group',
                                      $acache['@site'][$k]);
    }
  }
  foreach($DefaultPasswords as $k => $v) 
    list($page['=auth'][$k], $page['=passwd'][$k], $page['=pwsource'][$k]) =
      IsAuthorized(@$page["passwd$k"], 'page', $acache[$gn][$k]);
  foreach($AuthCascade as $k => $t) {
    if ($page['=auth'][$k]+0 == 2) {
      $page['=auth'][$k] = $page['=auth'][$t];
      if ($page['=passwd'][$k] = $page['=passwd'][$t])         # assign
        $page['=pwsource'][$k] = "cascade:$t";
    }
  }
  if (@$page['=auth']['admin']) 
    foreach($page['=auth'] as $lv=>$a) @$page['=auth'][$lv] = 3;
  if (@$page['=passwd']['read']) $NoHTMLCache |= 2;
  if ($level=='ALWAYS' || @$page['=auth'][$level]) return $page;
  if (!$authprompt) return false;
  $GLOBALS['AuthNeeded'] = (@$_POST['authpw']) 
    ? $page['=pwsource'][$level] . ' ' . $level : '';
  PCache($pagename, $page);
  $postvars = '';
  foreach($_POST as $k=>$v) {
    if ($k == 'authpw' || $k == 'authid') continue;
    $k = PHSC(stripmagic($k), ENT_QUOTES);
    if (is_array($v)) {
      foreach($v as $vk=>$vv) {
        $vk = PHSC(stripmagic($vk), ENT_QUOTES);
        $vv = str_replace('$', '&#036;', 
                PHSC(stripmagic($vv), ENT_COMPAT));
        $postvars .= "<input type='hidden' name='{$k}[{$vk}]' value=\"$vv\" />\n"; 
      }
    }
    else {
      $v = str_replace('$', '&#036;', 
              PHSC(stripmagic($v), ENT_COMPAT));
      $postvars .= "<input type='hidden' name='$k' value=\"$v\" />\n";
    }
  }
  $FmtV['$PostVars'] = $postvars;
  $r = str_replace("'", '%37', stripmagic($_SERVER['REQUEST_URI']));
  SDV($AuthPromptFmt,array(&$PageStartFmt,
    "<p><b>$[Password required]</b></p>
      <form name='authform' action='$r' method='post'>
        $[Password]: <input tabindex='1' type='password' name='authpw' 
          value='' />
        <input type='submit' value='$[OK]' />\$PostVars</form>
        <script language='javascript' type='text/javascript'><!--
          document.authform.authpw.focus() //--></script>", &$PageEndFmt));
  PrintFmt($pagename,$AuthPromptFmt);
  exit;
}

function IsAuthorized($chal, $source, &$from) {
  global $AuthList, $AuthPw, $AllowPassword;
  if (!$chal) return $from;
  $auth = 0; 
  $passwd = array();
  foreach((array)$chal as $c) {
    $x = '';
    $pwchal = preg_split('/([, ]|\\w+:)/', $c, -1, PREG_SPLIT_DELIM_CAPTURE);
    foreach($pwchal as $pw) {
      if ($pw == ',' || $pw == '') continue;
      else if ($pw == ' ') { $x = ''; continue; }
      else if (substr($pw, -1, 1) == ':') { $x = $pw; continue; }
      else if ($pw[0] != '@' && $x > '') $pw = $x . $pw;
      if (!$pw) continue;
      $passwd[] = $pw;
      if ($auth < 0) continue;
      if ($x || $pw[0] == '@') {
        if (@$AuthList[$pw]) $auth = $AuthList[$pw];
        continue;
      }
      if ($AllowPassword && pmcrypt($AllowPassword, $pw) == $pw) # nopass
        { $auth=1; continue; }
      foreach((array)$AuthPw as $pwresp)                         # password
        if (pmcrypt($pwresp, $pw) == $pw) { $auth=1; continue; }
    }
  }
  if (!$passwd) return $from;
  if ($auth < 0) $auth = 0;
  return array($auth, $passwd, $source);
}


## SessionAuth works with PmWikiAuth to manage authorizations
## as stored in sessions.  First, it can be used to set session
## variables by calling it with an $auth argument.  It then
## uses the authid, authpw, and authlist session variables
## to set the corresponding values of $AuthId, $AuthPw, and $AuthList
## as needed.
function SessionAuth($pagename, $auth = NULL) {
  global $AuthId, $AuthList, $AuthPw, $SessionEncode, $SessionDecode,
    $EnableSessionPasswords, $EnableAuthPostRegenerateSID;
  static $called;

  @$called++;
  $sn = session_name(); # in PHP5.3, $_REQUEST doesn't contain $_COOKIE
  if (!$auth && ($called > 1 || (!@$_REQUEST[$sn] && !@$_COOKIE[$sn]))) return;

  $sid = session_id();
  @session_start();
  if($called == 1 && isset($_POST['authpw']) && $_POST['authpw']
    && IsEnabled($EnableAuthPostRegenerateSID, true)) {
    session_regenerate_id();
  }
  
  foreach((array)$auth as $k => $v) {
    if ($k == 'authpw') {
      foreach((array)$v as $pw => $pv) {
        if ($SessionEncode) $pw = $SessionEncode($pw);
        $_SESSION[$k][$pw] = $pv;
      }
    } 
    else if ($k) $_SESSION[$k] = (array)$v + (array)@$_SESSION[$k];
  }

  if (!isset($AuthId)) $AuthId = @$_SESSION['authid'] ? @end($_SESSION['authid']) : '';
  $AuthPw = array_map($SessionDecode, array_keys((array)@$_SESSION['authpw']));
  if (!IsEnabled($EnableSessionPasswords, 1)) $_SESSION['authpw'] = array();
  $AuthList = array_merge($AuthList, (array)@$_SESSION['authlist']);
  
  if (!$sid) @session_write_close();
}


function PasswdVar($pagename, $level) {
  global $PCache, $PasswdVarAuth, $FmtV;
  $page = $PCache[$pagename];
  if (!isset($page['=passwd'][$level])) {
    $page = RetrieveAuthPage($pagename, 'ALWAYS', false, READPAGE_CURRENT);
    if ($page) PCache($pagename, $page);
  }
  SDV($PasswdVarAuth, 'attr');
  if ($PasswdVarAuth && !@$page['=auth'][$PasswdVarAuth]) return XL('(protected)');
  $pwsource = $page['=pwsource'][$level];
  if (strncmp($pwsource, 'cascade:', 8) == 0) {
    $FmtV['$PWCascade'] = substr($pwsource, 8);
    return FmtPageName('$[(using $PWCascade password)]', $pagename);
  }
  $setting = PHSC(implode(' ', preg_replace('/^(?!@|\\w+:).+$/', '****',
                                       (array)$page['=passwd'][$level])));
  if ($pwsource == 'group' || $pwsource == 'site') {
    $FmtV['$PWSource'] = $pwsource;
    $setting = FmtPageName('$[(set by $PWSource)] ', $pagename)
       . PHSC($setting);
  }
  return $setting;
}


function PrintAttrForm($pagename) {
  global $PageAttributes, $PCache, $FmtV;
  $FmtV['$TokenValue'] = pmtoken();
  echo FmtPageName("<form action='\$PageUrl' method='post'>
    <input type='hidden' name='action' value='postattr' />
    <input type='hidden' name='\$TokenName' value='\$TokenValue' />
    <input type='hidden' name='n' value='\$FullName' />
    <table>",$pagename);
  $page = $PCache[$pagename];
  foreach($PageAttributes as $attr=>$p) {
    if (!$p) continue;
    if (strncmp($attr, 'passwd', 6) == 0) {
      $setting = PageVar($pagename, '$Passwd'.ucfirst(substr($attr, 6)));
      $value = '';
    } else { $setting = $value = PHSC(@$page[$attr]); }
    $prompt = FmtPageName($p,$pagename);
    echo "<tr><td>$prompt</td>
      <td><input type='text' name='$attr' value='$value' /></td>
      <td>$setting</td></tr>";
  }
  echo FmtPageName("</table><input type='submit' value='$[Save]' /></form>",
         $pagename);
}

function HandleAttr($pagename, $auth = 'attr') {
  global $HandleAttrFmt,$PageAttrFmt,$PageStartFmt,$PageEndFmt;
  $page = RetrieveAuthPage($pagename, $auth, true, READPAGE_CURRENT);
  if (!$page) { Abort("?unable to read $pagename"); }
  PCache($pagename,$page);
  XLSDV('en', array('EnterAttributes' =>
    "Enter new attributes for this page below.  Leaving a field blank
    will leave the attribute unchanged.  To clear an attribute, enter
    'clear'."));
  SDV($PageAttrFmt,"<div class='wikiattr'>
    <h2 class='wikiaction'>$[{\$FullName} Attributes]</h2>
    <p>$[EnterAttributes]</p></div>");
  SDV($HandleAttrFmt,array(&$PageStartFmt,&$PageAttrFmt,
    'function:PrintAttrForm',&$PageEndFmt));
  PrintFmt($pagename,$HandleAttrFmt);
}

function HandlePostAttr($pagename, $auth = 'attr') {
  global $PageAttributes, $EnablePostAttrClearSession;
  if(! AutoCheckToken()) {
    Abort('? $[Token invalid or missing.]');
  }
  Lock(2);
  $page = RetrieveAuthPage($pagename, $auth, true);
  if (!$page) { Abort("?unable to read $pagename"); }
  foreach($PageAttributes as $attr=>$p) {
    $v = stripmagic(@$_POST[$attr]);
    if ($v == '') continue;
    if ($v=='clear') unset($page[$attr]);
    else if (strncmp($attr, 'passwd', 6) != 0) $page[$attr] = $v;
    else {
      $a = array();
      preg_match_all('/"[^"]*"|\'[^\']*\'|\\S+/', $v, $match);
      foreach($match[0] as $pw) 
        $a[] = preg_match('/^(@|\\w+:)/', $pw) ? $pw 
                   : pmcrypt(preg_replace('/^([\'"])(.*)\\1$/', '$2', $pw));
      if ($a) $page[$attr] = implode(' ',$a);
    }
  }
  WritePage($pagename,$page);
  Lock(0);
  if (IsEnabled($EnablePostAttrClearSession, 1)) {
    @session_start();
    unset($_SESSION['authid']);
    unset($_SESSION['authlist']);
    $_SESSION['authpw'] = array();
  }
  Redirect($pagename);
  exit;
} 


function HandleLogoutA($pagename, $auth = 'read') {
  global $LogoutRedirectFmt, $LogoutCookies;
  SDV($LogoutRedirectFmt, '$FullName');
  SDV($LogoutCookies, array());
  @session_start();
  $_SESSION = array();
  if ( session_id() != '' || isset($_COOKIE[session_name()]) )
    pmsetcookie(session_name(), '', time()-43200, '/');
  foreach ($LogoutCookies as $c)
    if (isset($_COOKIE[$c])) pmsetcookie($c, '', time()-43200, '/');
  session_destroy();
  Redirect(FmtPageName($LogoutRedirectFmt, $pagename));
}


function HandleLoginA($pagename, $auth = 'login') {
  global $AuthId, $DefaultPasswords;
  unset($DefaultPasswords['admin']);
  $prompt = @(!$_POST['authpw'] || ($AuthId != $_POST['authid']));
  $page = RetrieveAuthPage($pagename, $auth, $prompt, READPAGE_CURRENT);
  Redirect($pagename);
}

