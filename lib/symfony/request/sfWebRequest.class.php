<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) 2004-2006 Sean Kerr <sean@code-box.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebRequest class.
 *
 * This class manages web requests. It parses input from the request and store them as parameters.
 *
 * @package    symfony
 * @subpackage request
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Sean Kerr <sean@code-box.org>
 * @version    SVN: $Id: sfWebRequest.class.php 8816 2008-05-06 19:46:35Z fabien $
 */
class sfWebRequest extends sfRequest
{
  protected
    $languages              = null,
    $charsets               = null,
    $acceptableContentTypes = null,
    $pathInfoArray          = null,
    $relativeUrlRoot        = null,
    $getParameters          = null,
    $postParameters         = null,
    $requestParameters      = null,
    $formats                = array(),
    $format                 = null;

  /**
   * Initializes this sfRequest.
   *
   * @param  sfEventDispatcher  A sfEventDispatcher instance
   * @param  array         An associative array of initialization parameters
   * @param  array         An associative array of initialization attributes
   *
   * @return Boolean       true, if initialization completes successfully, otherwise false
   *
   * @throws <b>sfInitializationException</b> If an error occurs while initializing this sfRequest
   */
  public function initialize(sfEventDispatcher $dispatcher, $parameters = array(), $attributes = array())
  {
    parent::initialize($dispatcher, $parameters, $attributes);

    if (isset($_SERVER['REQUEST_METHOD']))
    {
      switch ($_SERVER['REQUEST_METHOD'])
      {
        case 'GET':
          $this->setMethod(self::GET);
          break;

        case 'POST':
          $this->setMethod(self::POST);
          break;

        case 'PUT':
          $this->setMethod(self::PUT);
          break;

        case 'DELETE':
          $this->setMethod(self::DELETE);
          break;

        case 'HEAD':
          $this->setMethod(self::HEAD);
          break;

        default:
          $this->setMethod(self::GET);
      }
    }
    else
    {
      // set the default method
      $this->setMethod(self::GET);
    }

    foreach ($this->getAttribute('formats', array()) as $format => $mimeTypes)
    {
      $this->setFormat($format, $mimeTypes);
    }

    // load parameters from GET/PATH_INFO/POST
    $this->loadParameters();
  }

  /**
   * Retrieves an array of file information.
   *
   * @param string A file name
   *
   * @return array An associative array of file information, if the file exists, otherwise null
   */
  public function getFile($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? $this->getFileValues($name) : null;
  }

  /**
   * Retrieves a file error.
   *
   * @param string A file name
   *
   * @return int One of the following error codes:
   *
   *             - <b>UPLOAD_ERR_OK</b>        (no error)
   *             - <b>UPLOAD_ERR_INI_SIZE</b>  (the uploaded file exceeds the
   *                                           upload_max_filesize directive
   *                                           in php.ini)
   *             - <b>UPLOAD_ERR_FORM_SIZE</b> (the uploaded file exceeds the
   *                                           MAX_FILE_SIZE directive that
   *                                           was specified in the HTML form)
   *             - <b>UPLOAD_ERR_PARTIAL</b>   (the uploaded file was only
   *                                           partially uploaded)
   *             - <b>UPLOAD_ERR_NO_FILE</b>   (no file was uploaded)
   */
  public function getFileError($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? $this->getFileValue($name, 'error') : UPLOAD_ERR_NO_FILE;
  }

  /**
   * Retrieves a file name.
   *
   * @param string A file nam.
   *
   * @return string A file name, if the file exists, otherwise null
   */
  public function getFileName($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? $this->getFileValue($name, 'name') : null;
  }

  /**
   * Retrieves an array of file names.
   *
   * @return array An indexed array of file names
   */
  public function getFileNames()
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return array_keys($_FILES);
  }

  /**
   * Retrieves an array of files.
   *
   * @param  string A key
   * @return array  An associative array of files
   */
  public function getFiles($key = null)
  {
    return is_null($key) ? $_FILES : (isset($_FILES[$key]) ? $_FILES[$key] : array());
  }

  /**
   * Retrieves a file path.
   *
   * @param string A file name
   *
   * @return string A file path, if the file exists, otherwise null
   */
  public function getFilePath($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? $this->getFileValue($name, 'tmp_name') : null;
  }

  /**
   * Retrieve a file size.
   *
   * @param string A file name
   *
   * @return int A file size, if the file exists, otherwise null
   */
  public function getFileSize($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? $this->getFileValue($name, 'size') : null;
  }

  /**
   * Retrieves a file type.
   *
   * This may not be accurate. This is the mime-type sent by the browser
   * during the upload.
   *
   * @param string A file name
   *
   * @return string A file type, if the file exists, otherwise null
   */
  public function getFileType($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? $this->getFileValue($name, 'type') : null;
  }

  /**
   * Indicates whether or not a file exists.
   *
   * @param string A file name
   *
   * @return boolean true, if the file exists, otherwise false
   */
  public function hasFile($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    if (preg_match('/^(.+?)\[(.+?)\]$/', $name, $match))
    {
      return isset($_FILES[$match[1]]['name'][$match[2]]);
    }
    else
    {
      return isset($_FILES[$name]);
    }
  }

  /**
   * Indicates whether or not a file error exists.
   *
   * @param string A file name
   *
   * @return boolean true, if the file error exists, otherwise false
   */
  public function hasFileError($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return $this->hasFile($name) ? ($this->getFileValue($name, 'error') != UPLOAD_ERR_OK) : false;
  }

  /**
   * Indicates whether or not any file errors occured.
   *
   * @return boolean true, if any file errors occured, otherwise false
   */
  public function hasFileErrors()
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    foreach ($this->getFileNames() as $name)
    {
      if ($this->hasFileError($name) === true)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Indicates whether or not any files exist.
   *
   * @return boolean true, if any files exist, otherwise false
   */
  public function hasFiles()
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    return (count($_FILES) > 0);
  }

  /**
   * Retrieves a file value.
   *
   * @param string A file name
   * @param string Value to search in the file
   * 
   * @return string File value
   */
  public function getFileValue($name, $key)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    if (preg_match('/^(.+?)\[(.+?)\]$/', $name, $match))
    {
      return $_FILES[$match[1]][$key][$match[2]];
    }
    else
    {
      return $_FILES[$name][$key];
    }
  }

  /**
   * Retrieves all the values from a file.
   *
   * @param string A file name
   *
   * @return array Associative list of the file values
   */
  public function getFileValues($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    if (preg_match('/^(.+?)\[(.+?)\]$/', $name, $match))
    {
      return array(
        'name'     => $_FILES[$match[1]]['name'][$match[2]],
        'type'     => $_FILES[$match[1]]['type'][$match[2]],
        'tmp_name' => $_FILES[$match[1]]['tmp_name'][$match[2]],
        'error'    => $_FILES[$match[1]]['error'][$match[2]],
        'size'     => $_FILES[$match[1]]['size'][$match[2]],
      );
    }
    else
    {
      return $_FILES[$name];
    }
  }

  /**
   * Retrieves an extension for a given file.
   *
   * @param string A file name
   *
   * @return string Extension for the file
   */
  public function getFileExtension($name)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    static $mimeTypes = null;

    $fileType = $this->getFileType($name);

    if (!$fileType)
    {
      return '.bin';
    }

    if (is_null($mimeTypes))
    {
      $mimeTypes = unserialize(file_get_contents(sfConfig::get('sf_symfony_lib_dir').'/plugins/sfCompat10Plugin/data/mime_types.dat'));
    }

    return isset($mimeTypes[$fileType]) ? '.'.$mimeTypes[$fileType] : '.bin';
  }

  /**
   * Retrieves the uniform resource identifier for the current web request.
   *
   * @return string Unified resource identifier
   */
  public function getUri()
  {
    $pathArray = $this->getPathInfoArray();

    if ($this->isAbsUri())
    {
      return $pathArray['REQUEST_URI'];
    }

    return $this->getUriPrefix().$pathArray['REQUEST_URI'];
  }

  /**
   * See if the client is using absolute uri
   *
   * @return boolean true, if is absolute uri otherwise false
   */
  public function isAbsUri()
  {
    $pathArray = $this->getPathInfoArray();

    return preg_match('/^http/', $pathArray['REQUEST_URI']);
  }

  /**
   * Returns Uri prefix, including protocol, hostname and server port.
   *
   * @return string Uniform resource identifier prefix
   */
  public function getUriPrefix()
  {
    $pathArray = $this->getPathInfoArray();
    if ($this->isSecure())
    {
      $standardPort = '443';
      $proto = 'https';
    }
    else
    {
      $standardPort = '80';
      $proto = 'http';
    }

    $port = $pathArray['SERVER_PORT'] == $standardPort || !$pathArray['SERVER_PORT'] ? '' : ':'.$pathArray['SERVER_PORT'];

    return $proto.'://'.$pathArray['SERVER_NAME'].$port;
  }

  /**
   * Retrieves the path info for the current web request.
   *
   * @return string Path info
   */
  public function getPathInfo()
  {
    $pathInfo = '';

    $pathArray = $this->getPathInfoArray();

    // simulate PATH_INFO if needed
    $sf_path_info_key = sfConfig::get('sf_path_info_key', 'PATH_INFO');
    if (!isset($pathArray[$sf_path_info_key]) || !$pathArray[$sf_path_info_key])
    {
      if (isset($pathArray['REQUEST_URI']))
      {
        $script_name = $this->getScriptName();
        $uri_prefix = $this->isAbsUri() ? $this->getUriPrefix() : '';
        $pathInfo = preg_replace('/^'.preg_quote($uri_prefix, '/').'/','',$pathArray['REQUEST_URI']);
        $pathInfo = preg_replace('/^'.preg_quote($script_name, '/').'/', '', $pathInfo);
        $prefix_name = preg_replace('#/[^/]+$#', '', $script_name);
        $pathInfo = preg_replace('/^'.preg_quote($prefix_name, '/').'/', '', $pathInfo);
        $pathInfo = preg_replace('/'.preg_quote($pathArray['QUERY_STRING'], '/').'$/', '', $pathInfo);
      }
    }
    else
    {
      $pathInfo = $pathArray[$sf_path_info_key];
      if ($sf_relative_url_root = $this->getRelativeUrlRoot())
      {
        $pathInfo = preg_replace('/^'.str_replace('/', '\\/', $sf_relative_url_root).'\//', '', $pathInfo);
      }
    }

    // for IIS
    if (isset($_SERVER['SERVER_SOFTWARE']) && false !== stripos($_SERVER['SERVER_SOFTWARE'], 'iis') && $pos = stripos($pathInfo, '.php'))
    {
      $pathInfo = substr($pathInfo, $pos + 4);
    }

    if (!$pathInfo)
    {
      $pathInfo = '/';
    }

    return $pathInfo;
  }

  public function getGetParameters()
  {
    return $this->getParameters;
  }

  public function getPostParameters()
  {
    return $this->postParameters;
  }

  public function getRequestParameters()
  {
    return $this->requestParameters;
  }

  /**
   * Moves an uploaded file.
   *
   * @param string A file name
   * @param string An absolute filesystem path to where you would like the
   *               file moved. This includes the new filename as well, since
   *               uploaded files are stored with random names
   * @param int    The octal mode to use for the new file
   * @param boolean   Indicates that we should make the directory before moving the file
   * @param int    The octal mode to use when creating the directory
   *
   * @return boolean true, if the file was moved, otherwise false
   *
   * @throws <b>sfFileException</b> If a major error occurs while attempting to move the file
   */
  public function moveFile($name, $file, $fileMode = 0666, $create = true, $dirMode = 0777)
  {
    if (!sfConfig::get('sf_compat_10'))
    {
      throw new sfConfigurationException('You must set "compat_10" to true if you want to use this method which is deprecated.');
    }

    if ($this->hasFile($name) && $this->getFileValue($name, 'error') == UPLOAD_ERR_OK && $this->getFileValue($name, 'size') > 0)
    {
      // get our directory path from the destination filename
      $directory = dirname($file);

      if (!is_readable($directory))
      {
        $fmode = 0777;

        if ($create && !@mkdir($directory, $dirMode, true))
        {
          // failed to create the directory
          throw new sfFileException(sprintf('Failed to create file upload directory "%s".', $directory));
        }

        // chmod the directory since it doesn't seem to work on
        // recursive paths
        @chmod($directory, $dirMode);
      }
      else if (!is_dir($directory))
      {
        // the directory path exists but it's not a directory
        throw new sfFileException(sprintf('File upload path "%s" exists, but is not a directory.', $directory));
      }
      else if (!is_writable($directory))
      {
        // the directory isn't writable
        throw new sfFileException(sprintf('File upload path "%s" is not writable.', $directory));
      }

      if (@move_uploaded_file($this->getFileValue($name, 'tmp_name'), $file))
      {
        // chmod our file
        @chmod($file, $fileMode);

        return true;
      }
    }

    return false;
  }

  /**
   * Returns referer.
   *
   * @return  string
   */
  public function getReferer()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['HTTP_REFERER']) ? $pathArray['HTTP_REFERER'] : '';
  }

  /**
   * Returns current host name.
   *
   * @return  string
   */
  public function getHost()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['HTTP_X_FORWARDED_HOST']) ? $pathArray['HTTP_X_FORWARDED_HOST'] : (isset($pathArray['HTTP_HOST']) ? $pathArray['HTTP_HOST'] : '');
  }

  /**
   * Returns current script name.
   *
   * @return  string
   */
  public function getScriptName()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['SCRIPT_NAME']) ? $pathArray['SCRIPT_NAME'] : (isset($pathArray['ORIG_SCRIPT_NAME']) ? $pathArray['ORIG_SCRIPT_NAME'] : '');
  }

  /**
   * Checks if the request method is the given one.
   *
   * @param  string  The method name
   *
   * @return Boolean true if the current method is the given one, false otherwise
   */
  public function isMethod($method)
  {
    $pathArray = $this->getPathInfoArray();

    return strtolower($method) == strtolower($this->getMethodName());
  }

  /**
   * Returns request method.
   *
   * @return  string
   */
  public function getMethodName()
  {
    $pathArray = $this->getPathInfoArray();

    return isset($pathArray['REQUEST_METHOD']) ? $pathArray['REQUEST_METHOD'] : 'GET';
  }

  /**
   * Returns the preferred culture for the current request.
   *
   * @param  array  An array of ordered cultures available
   *
   * @return string The preferred culture
   */
  public function getPreferredCulture(array $cultures = null)
  {
    $preferredCultures = $this->getLanguages();

    if (is_null($cultures))
    {
      return isset($preferredCultures[0]) ? $preferredCultures[0] : null;
    }

    if (!$preferredCultures)
    {
      return $cultures[0];
    }

    $preferredCultures = array_values(array_intersect($preferredCultures, $cultures));

    return isset($preferredCultures[0]) ? $preferredCultures[0] : $cultures[0];
  }

  /**
   * Gets a list of languages acceptable by the client browser
   *
   * @return array Languages ordered in the user browser preferences
   */
  public function getLanguages()
  {
    if ($this->languages)
    {
      return $this->languages;
    }

    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
    {
      return array();
    }

    $languages = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($languages as $lang)
    {
      if (strstr($lang, '-'))
      {
        $codes = explode('-', $lang);
        if ($codes[0] == 'i')
        {
          // Language not listed in ISO 639 that are not variants
          // of any listed language, which can be registerd with the
          // i-prefix, such as i-cherokee
          if (count($codes) > 1)
          {
            $lang = $codes[1];
          }
        }
        else
        {
          for ($i = 0, $max = count($codes); $i < $max; $i++)
          {
            if ($i == 0)
            {
              $lang = strtolower($codes[0]);
            }
            else
            {
              $lang .= '_'.strtoupper($codes[$i]);
            }
          }
        }
      }

      $this->languages[] = $lang;
    }

    return $this->languages;
  }

  /**
   * Gets a list of charsets acceptable by the client browser.
   *
   * @return array List of charsets in preferable order
   */
  public function getCharsets()
  {
    if ($this->charsets)
    {
      return $this->charsets;
    }

    if (!isset($_SERVER['HTTP_ACCEPT_CHARSET']))
    {
      return array();
    }

    $this->charsets = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT_CHARSET']);

    return $this->charsets;
  }

  /**
   * Gets a list of content types acceptable by the client browser
   *
   * @return array Languages ordered in the user browser preferences
   */
  public function getAcceptableContentTypes()
  {
    if ($this->acceptableContentTypes)
    {
      return $this->acceptableContentTypes;
    }

    if (!isset($_SERVER['HTTP_ACCEPT']))
    {
      return array();
    }

    $this->acceptableContentTypes = $this->splitHttpAcceptHeader($_SERVER['HTTP_ACCEPT']);

    return $this->acceptableContentTypes;
  }

  /**
   * Returns true if the request is a XMLHttpRequest.
   *
   * It works if your JavaScript library set an X-Requested-With HTTP header.
   * Works with Prototype, Mootools, jQuery, and perhaps others.
   *
   * @return Boolean true if the request is an XMLHttpRequest, false otherwise
   */
  public function isXmlHttpRequest()
  {
    return ($this->getHttpHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
  }

  public function getHttpHeader($name, $prefix = 'http')
  {
    if ($prefix)
    {
      $prefix = strtoupper($prefix).'_';
    }

    $name = $prefix.strtoupper(strtr($name, '-', '_'));

    $pathArray = $this->getPathInfoArray();

    return isset($pathArray[$name]) ? stripslashes($pathArray[$name]) : null;
  }

  /**
   * Gets a cookie value.
   *
   * @return mixed
   */
  public function getCookie($name, $defaultValue = null)
  {
    $retval = $defaultValue;

    if (isset($_COOKIE[$name]))
    {
      $retval = get_magic_quotes_gpc() ? stripslashes($_COOKIE[$name]) : $_COOKIE[$name];
    }

    return $retval;
  }

  /**
   * Returns true if the current request is secure (HTTPS protocol).
   *
   * @return boolean
   */
  public function isSecure()
  {
    $pathArray = $this->getPathInfoArray();

    return (
      (isset($pathArray['HTTPS']) && (strtolower($pathArray['HTTPS']) == 'on' || strtolower($pathArray['HTTPS']) == 1))
      ||
      (isset($pathArray['HTTP_X_FORWARDED_PROTO']) && strtolower($pathArray['HTTP_X_FORWARDED_PROTO']) == 'https')
    );
  }

  /**
   * Retrieves relative root url.
   *
   * @return string URL
   */
  public function getRelativeUrlRoot()
  {
    if ($this->relativeUrlRoot === null)
    {
      $this->relativeUrlRoot = sfConfig::get('sf_relative_url_root', preg_replace('#/[^/]+\.php5?$#', '', $this->getScriptName()));
    }

    return $this->relativeUrlRoot;
  }

  /**
   * Sets the relative root url for the current web request.
   *
   * @param string Value for the url
   */
  public function setRelativeUrlRoot($value)
  {
    $this->relativeUrlRoot = $value;
  }

  /**
   * Splits an HTTP header for the current web request.
   *
   * @param string Header to split
   */
  public function splitHttpAcceptHeader($header)
  {
    $values = array();
    foreach (array_filter(explode(',', $header)) as $value)
    {
      // Cut off any q-value that might come after a semi-colon
      if ($pos = strpos($value, ';'))
      {
        $q     = (float) trim(substr($value, $pos + 3));
        $value = trim(substr($value, 0, $pos));
      }
      else
      {
        $q = 1;
      }

      $values[$value] = $q;
    }

    arsort($values);

    return array_keys($values);
  }

  /**
   * Returns the array that contains all request information ($_SERVER or $_ENV).
   *
   * This information is stored in the [sf_path_info_array] constant.
   *
   * @return  array Path information
   */
  protected function getPathInfoArray()
  {
    if (!$this->pathInfoArray)
    {
      // parse PATH_INFO
      switch (sfConfig::get('sf_path_info_array', 'SERVER'))
      {
        case 'SERVER':
          $this->pathInfoArray =& $_SERVER;
          break;

        case 'ENV':
        default:
          $this->pathInfoArray =& $_ENV;
      }
    }

    return $this->pathInfoArray;
  }

  /**
   * Gets the mime type associated with the format.
   *
   * @param  string The format
   *
   * @return string The associated mime type (null if not found)
   */
  public function getMimeType($format)
  {
    return isset($this->formats[$format]) ? $this->formats[$format][0] : null;
  }

  /**
   * Gets the format associated with the mime type.
   *
   * @param  string The associated mime type
   *
   * @return string The format (null if not found)
   */
  public function getFormat($mimeType)
  {
    foreach ($this->formats as $format => $mimeTypes)
    {
      if (in_array($mimeType, $mimeTypes))
      {
        return $format;
      }
    }

    return null;
  }

  /**
   * Associates a format with mime types.
   *
   * @param string       The format
   * @param string|array The associated mime types (the preferred one must be the first as it will be used as the content type)
   */
  public function setFormat($format, $mimeTypes)
  {
    $this->formats[$format] = is_array($mimeTypes) ? $mimeTypes : array($mimeTypes);
  }

  /**
   * Sets the request format.
   *
   * @param string The request format
   */
  public function setRequestFormat($format)
  {
    $this->format = $format;
  }

  /**
   * Gets the request format.
   *
   * If no format is defined by the user, it defaults to the sf_format request parameter if available.
   *
   * @return string The request format
   */
  public function getRequestFormat()
  {
    if (is_null($this->format))
    {
      if ($this->getParameter('sf_format'))
      {
        $this->setRequestFormat($this->getParameter('sf_format'));
      }
      else
      {
        $acceptableContentTypes = $this->getAcceptableContentTypes();

        // skip if no acceptable content types or browsers
        if (isset($acceptableContentTypes[0]) && 'text/xml' != $acceptableContentTypes[0])
        {
          $this->setRequestFormat($this->getFormat($acceptableContentTypes[0]));
        }
      }
    }

    return $this->format;
  }

  /**
   * Returns the value of a GET parameter.
   *
   * @param  string The GET parameter name
   * @param  string The default value
   *
   * @return string The GET parameter value
   */
  public function getGetParameter($name, $default = null)
  {
    if (isset($this->getParameters[$name]))
    {
      return $this->getParameters[$name];
    }
    else
    {
      return sfToolkit::getArrayValueForPath($this->getParameters, $name, $default);
    }
  }

  /**
   * Returns the value of a POST parameter.
   *
   * @param  string The POST parameter name
   * @param  string The default value
   *
   * @return string The POST parameter value
   */
  public function getPostParameter($name, $default = null)
  {
    if (isset($this->postParameters[$name]))
    {
      return $this->postParameters[$name];
    }
    else
    {
      return sfToolkit::getArrayValueForPath($this->postParameters, $name, $default);
    }
  }

  /**
   * Returns the value of a parameter passed as a URL segment.
   *
   * @param  string The parameter name
   * @param  string The default value
   *
   * @return string The parameter value
   */
  public function getUrlParameter($name, $default = null)
  {
    if (isset($this->requestParameters[$name]))
    {
      return $this->requestParameters[$name];
    }
    else
    {
      return sfToolkit::getArrayValueForPath($this->requestParameters, $name, $default);
    }
  }

  /**
   * Parses the request parameters.
   *
   * This method notifies the request.filter_parameters event.
   *
   * @return array An array of request parameters.
   */
  protected function parseRequestParameters()
  {
    return $this->dispatcher->filter(new sfEvent($this, 'request.filter_parameters', array('path_info' => $this->getPathInfo())), array())->getReturnValue();
  }

  /**
   * Loads GET, PATH_INFO and POST data into the parameter list.
   *
   */
  protected function loadParameters()
  {
    // GET parameters
    $this->getParameters = get_magic_quotes_gpc() ? sfToolkit::stripslashesDeep($_GET) : $_GET;
    $this->parameterHolder->add($this->getParameters);

    // additional parameters
    $this->requestParameters = $this->parseRequestParameters();
    $this->parameterHolder->add($this->requestParameters);

    // POST parameters
    $this->postParameters = get_magic_quotes_gpc() ? sfToolkit::stripslashesDeep($_POST) : $_POST;
    $this->parameterHolder->add($this->postParameters);

    // move symfony parameters to attributes (parameters prefixed with _sf_)
    foreach ($this->parameterHolder->getAll() as $key => $value)
    {
      if (0 === stripos($key, '_sf_'))
      {
        $this->parameterHolder->remove($key);
        $this->setAttribute($key, $value);
      }
    }

    if (sfConfig::get('sf_logging_enabled'))
    {
      $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Request parameters %s', str_replace("\n", '', var_export($this->getParameterHolder()->getAll(), true))))));
    }
  }
}
