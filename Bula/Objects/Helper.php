<?php
/**
 * Buddy Fetcher: simple RSS-fetcher/aggregator.
 *
 * @author Buddy Lancer <http://www.buddylancer.com>
 * @copyright 2020 Buddy Lancer
 * @version 0.1
 * @license MIT
 */
namespace Bula\Objects;

use Bula\Objects\TString;
use Bula\Objects\Enumerator;

require_once("TString.php");

/**
 * Helper class for manipulation with Files and Directories.
 */
class Helper {
    private static $last_error = null;

	/**
     * Get last error (if any).
     * @return TString Last error message.
     */
    public static function lastError() {
        return $last_error;
    }

    /**
     * Check whether file exists.
     * @param TString $path File name.
     * @return Boolean
     */
    public static function fileExists($path) {
		return file_exists(CAT($path)) && self::isFile(CAT($path));
	}

	/**
     * Check whether file exists.
     * @param TString $path File name.
     * @return Boolean
     */
	public static function dirExists($path) {
		return file_exists(CAT($path)) && self::isDir(CAT($path));
	}

    /**
     * Create directory.
     * @param TString $path Directory path to create.
     * @return Boolean True - created OK, False - error.
     */
    public static function createDir($path) {
		return mkdir(CAT($path));

	}

	/**
     * Delete file.
     * @param TString $path File name.
     * @return Boolean True - OK, False - error.
     */
    public static function deleteFile($path) {
		return unlink(CAT($path));

	}

	/**
     * Delete directory (recursively).
     * @param TString $path Directory name.
     * @return Boolean True - OK, False - error.
     */
	public static function deleteDir($path) {
		if ($path instanceof TString) $path = $path->getValue();

        if (!self::dirExists($path))
            return false;

        $entries = self::listDirEntries($path);
        while ($entries->moveNext()) {
            $entry = CAT($entries->current());

            if (self::isFile($entry))
                self::deleteFile($entry);
            else if (self::isDir($entry))
                self::deleteDir($entry);
        }
		return self::removeDir($path);
	}

	/**
     * Remove directory.
     * @param TString $path Directory name.
     * @return Boolean True - OK, False - error.
     */
    public static function removeDir($path) {
        return rmdir(CAT($path));

    }

	/**
     * Read all content of text file.
     * @param TString $filename File name.
     * @param TString $encoding Encoding name [optional].
     * @return TString Resulting content.
     */
    public static function readAllText($filename, $encoding = null) {
		return new TString(file_get_contents(CAT($filename)));
	}

  	/**
     * Read all content of text file as list of lines.
     * @param TString $filename File name.
     * @param TString $encoding Encoding name [optional].
     * @return Object[] Resulting content (lines).
     */
    public static function readAllLines($filename, $encoding = null) {
		return file(CAT($filename));

	}

	/**
     * Write content to text file.
     * @param TString $filename File name.
     * @param TString $text Content to write.
     * @return Boolean Result of operation (true - OK, false - error).
     */
    public static function writeText($filename, $text) {
        file_put_contents(CAT($filename), CAT($text)) !== false;
    }

	/**
     * Append content to text file.
     * @param TString $filename File name.
     * @param TString $text Content to append.
     * @return Boolean Result of operation (true - OK, false - error).
     */
    public static function appendText($filename, $text) {
        file_put_contents(CAT($filename), CAT($text), FILE_APPEND) !== false;
    }

    /**
     * Check whether given path is a file.
     * @param TString $path Path of an object.
     * @return Boolean True - is a file.
     */
    public static function isFile($path) {
        return is_file(CAT($path));
    }

    /**
     * Check whether given path is a directory.
     * @param TString $path Path of an object.
     * @return Boolean True - is a directory.
     */
    public static function isDir($path) {
        return is_dir(CAT($path));
    }

    /**
     * List (enumerate) entries of a given path.
     * @param TString $path Path of a directory.
     * @return Enumerator Enumerated entries.
     */
//if php
    public static function listDirEntries($path) {
		if (($handle = opendir(CAT($path))) == null)
            return null;
        $entries = new ArrayList();
        while (false !== ($file = readdir($handle))) {
            if ($file == "." || $file == "..")
                continue;
            $path2 = CAT($path, "/", $file);
            $entries->add($path2);
        }
        closedir($handle);
        return new Enumerator($entries->toArray());
    }

}
