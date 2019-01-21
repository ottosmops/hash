<?php

/**
 * hash create and verify hash files
 *
 * PHP version >=7.1
 *
 * @category File-Verification
 * @package  hash
 * @author   andreas kraenzle <kraenzle@k-r.ch>
 * @license  https://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link     https://github.com/ottosmops/hash
 */

namespace Ottosmops\Hash;

use Ottosmops\Hash\Exceptions\AlgorithmNotFound;
use Ottosmops\Hash\Exceptions\FileNotFound;
use Ottosmops\Hash\Exceptions\DirNotFound;
use Ottosmops\Hash\Exceptions\SeparatorNotFound;

/**
 *  Hash
 *
 * @category Class
 * @package  Hash
 * @author   andreas kraenzle <kraenzle@k-r.ch>
 */
class Hash
{
    public $manifest;

    public $dir;

    public $lines = [];

    public $messages = [];

    public $algorithm;

    public function __construct(string $algorithm = 'md5')
    {
        if (!in_array($algorithm, hash_algos())) {
            throw new AlgorithmNotFound("Did not find the hash algorithm {$algorithm}");
            return false;
        }

        $this->algorithm = $algorithm;
    }

    /**
     * createChecksums
     * @param  string $dir     path to dir
     * @param  string $manifest optional path to a hash-file, it must be relative to dir
     * @return int             line count
     */
    public function createManifest(string $dir, string $manifest = '', bool $deep = true)
    {
        $this->dir = realpath($dir) .'/';

        if (!is_dir($this->dir)) {
            throw new DirNotFound("Could not find directory {$dir}");
        }

        $this->manifest = ($manifest == '') ? $this->dir . 'manifest' : $this->dir . $manifest;

        $manifest_pathinfo = pathinfo($this->manifest);
        $manifest_dir = $manifest_pathinfo['dirname'];

        $lcn = 0;
        foreach ($this->filelist($dir, $deep) as $file) {
            if (is_file($file) && ($file != $this->manifest)) {
                $lcn ++;
                $lines[] = join('  ', [hash_file($this->algorithm, $file), $this->trimPath($this->getRelativePath($manifest_dir, $file))]);
            }
        }
        file_put_contents($this->manifest, implode("\n", $lines));

        return $lcn;
    }

    public function verifyManifest($manifest)
    {
        if (!is_file($manifest) && !is_readable($manifest)) {
            throw new FileNotFound("could not find or open file {$manifest}");
        }
        $this->manifest = realpath($manifest);
        $this->readLines();
        return $this->checkLines();
    }

    private function readLines()
    {
        $fh = fopen($this->manifest, "r");
        $sep = '';
        $lc = 0;
        while (!feof($fh)) {
            $lc++;
            $line = fgets($fh);
            if (empty($line)) {
                continue;
            }

            $sep = $this->detectSeparator($line);

            $this->lines[$lc] = explode($sep, trim($line));
            $this->lines[$lc]['binary'] = $sep === ' *' ? true : false;
        }
        fclose($fh);
    }

    private function checkLines()
    {
        $cwd_old = getcwd();
        chdir(pathinfo($this->manifest)['dirname']);

        foreach ($this->lines as $k => $line) {
            if (!is_file($line[1])) {
                $this->messages[$k] = sprintf('error line %d: could not find %s', $k, $line[1]);
                continue;
            }
            if (!$this->checkLine($line)) {
                $this->messages[$k] = sprintf('error line %d: could not verify line %s  %s', $k, $line[0], $line[1]);
            }
        }

        chdir($cwd_old);

        return count($this->messages) ? false : true;
    }

    private function checkLine($line)
    {
        $file = $line[1];
        return hash_file($this->algorithm, $file) == $line[0];
    }

    private function trimPath($path)
    {
        if (substr($path, 0, 2) == './') {
            $path = substr($path, strlen('./'));
        }
        return $path;
    }

    /**
     * [detectSeparator description]
     * @param  string $line
     * @return string '  ' or ' *' or false
     */
    private function detectSeparator($line)
    {
        if (preg_match('|(\s+\*?)|', $line, $matches)) {
            return $matches[0];
        } else {
            throw new SeparatorNotFound("could not detect an apropriate sepataror $line");
        }
        return $sep;
    }

    private function filelist($dir, $deep = true)
    {
        $array = [];
        if ($deep) {
            foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir)) as $filename) {
                if (is_file($filename)) {
                    $array[] = realpath("$filename");
                }
            }
        } else {
            foreach (glob($dir.'/*') as $filename) {
                if (is_file($filename)) {
                    $array[] = realpath("$filename");
                }
            }
        }
        return $array;
    }

    /**
     * [getRelativePath description]
     * from http://stackoverflow.com/questions/2637945/getting-relative-path-from-absolute-path-in-php
     * @param  string $from absolute path
     * @param  string $to   absolute path
     * @return string       relativ path
     */
    private function getRelativePath($from, $to)
    {
        // some compatibility fixes for Windows paths
        $from = is_dir($from) ? rtrim($from, '\/') . '/' : $from;
        $to   = is_dir($to)   ? rtrim($to, '\/') . '/'   : $to;
        $from = str_replace('\\', '/', $from);
        $to   = str_replace('\\', '/', $to);

        $from     = explode('/', $from);
        $to       = explode('/', $to);
        $relPath  = $to;

        foreach ($from as $depth => $dir) {
            // find first non-matching dir
            if ($dir === $to[$depth]) {
                // ignore this directory
                array_shift($relPath);
            } else {
                // get number of remaining dirs to $from
                $remaining = count($from) - $depth;
                if ($remaining > 1) {
                    // add traversals up to first matching dir
                    $padLength = (count($relPath) + $remaining - 1) * -1;
                    $relPath = array_pad($relPath, $padLength, '..');
                    break;
                } else {
                    $relPath[0] = './' . $relPath[0];
                }
            }
        }
        return implode('/', $relPath);
    }
}
