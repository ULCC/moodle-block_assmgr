<?php

/**
 * Class to capture a snapshot of a web site in order to add it to a candidate's portfolio
 *
 * @author http://www.ulcc.ac.uk, http://moodle.ulcc.ac.uk
 * @copyright &copy; 2009-2010 University of London Computer Centre
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package AssMgr
 * @version 2.0
 */
class   assmgr_web_capture {

    public      $wget_available;
    private     $first_page;
    private     $save_path;
    private     $save_dir;

    /**
     * Constructor. Checks to see if wget is available.
     *
     * @return void
     */
    function __construct() {
        $wget_available =   $this->web_capture_available();
    }

    /**
     * This function returns true or false depending on whether the wget program
     * is available on the system that the script is being executed on
     *
     * @return boolean true or false depending on whether the wget program is on
     *                 the current system
     */
    function web_capture_available() {
        $result   =    exec('wget');
        return  (empty($result)) ? false  :  true;
    }

    /**
     * Creates a object containing all evidence pertaining to the portfolio with the given id
     *
     * @param string  $url the url of the web site that will be downloaded
     * @param string  $savedir the name of the directory to save the retrieved site in
     * @param object  $param the parameters object, which can take the following optional parameters as object properties:
     * @param boolean $ignore_robots (property of $param) should wget honour information in a sites robot file defaults to true
     * @param int     $retrys (property of $param) the maximum number of times to retry before giving up on a link defaults to 2
     * @param int     $depth (property of $param)  the maximum depth at which to download a page and its links defaults to 5
     * @param int     $timeout (property of $param) the maximum number of time to wait before timing out an operation
     *                (in seconds) default to 60 seconds
     * @param int     $download_rate (property of $param) the kilobyte file size rate at which to download file chunks default to 100
     * @param boolean $random_wait (property of $param)   whether a random time should be waited in between downloading files
     * @param boolean $adjust_extensions (property of $param) whether file of type applicatio/xhtml+xml or text/html should have their
     *                file extension adjusted. useful for .php and .asp files defaults true
     * @param boolean $non_cached (property of $param) whether non cached files should be returned by the server default true
     * @param boolean $ignore_length (property of $param) should content headers detailing a files length be ignored default true
     * @param array   $acceptted_files (property of $param) array containing file extensions that are acceptable defaut NULL
     * @param array   $rejected_files (property of $param) array containing file extensions that will not be allowed
     *                to download defaut NULL
     * @param boolean $host_spanning (property of $param) should be allowed -n note this depends on the depth limit given
     * @param boolean $recurise (property of $param) should the file files be downloaded
     *                recursivly default true
     * @param int     $limit (property of $param) the maximum filesize the total download can be Note this
     *                only works if more than one file is downloaded
     * @param boolean $local_links (property of $param) should links be converted into local links
     * @param boolean $page_requisites (property of $param) should we get all files (images, css etc) need to display a page
     *
     * @return int 0 if capture failed, 1 if capture completed, 2 if capture exceed download limit
     */
    function capture_site($url, $savedir, $param) {


        $paramstr  =   '';
        /*
        if (!empty($param['ignore_robots']))   {
            $paramstr   =   (empty($param['ignore_robots']))    ?  ' robot=off ' : ' robot=on ';
        }
        */
        if (!empty($param['limit'])) {

            if (is_int($param['limit'])) $paramstr   .= "-Q {$param['limit']} ";
        }

        if (!empty($param['retrys'])) {

            if (is_int($param['retrys'])) $paramstr   .= "--tries={$param['retrys']} ";
        }

        if (!empty($param['timeout'])) {

            if (is_int($param['timeout'])) $paramstr   .= "-T {$param['timeout']} ";
        }

        if (!empty($param['download_rate'])) {

            if (is_int($param['download_rate'])) $paramstr   .= "--limit-rate={$param['download_rate']} ";
        }

        if (!empty($param['adjust_extensions'])) {
            $paramstr   .=  '-E ';
        }

        if (!empty($param['non_cached'])) {
            $paramstr   .=  '--no-cache ';
        }

        if (!empty($param['ignore_length'])) {
            $paramstr   .=  '--ignore-length ';
        }

        if (!empty($param['acceptted_files'])) {

            if (is_array($param['acceptted_files'])) {
                $accepted   =   implode($param['acceptted_files'], ',');
                $paramstr   .=  " -A {$accepted} ";
            }

        }

        if (!empty($param['rejected_files'])) {

            if (is_array($param['rejected_files'])) {
                $rejected   =   implode($param['rejected_files'], ',');
                $paramstr   .=  " -R {$rejected} ";
            }

        }

        if (!empty($param['host_spanning'])) {
            $paramstr   .=  ' -H ';
        }

        if (!empty($param['recursive'])) {
            $paramstr   .=  ' -r ';

            if (!empty($param['depth'])) {

                if (is_int($param['depth'])) $paramstr   .= " -l {$param['depth']} ";
            }
        }

        if (!empty($param['local_links'])) {
            $paramstr   .=  ' -k ';
        }

        if (!empty($param['page_requisites'])) {
            $paramstr   .=  ' -p ';
        }

        return $this->process_command("wget -nv {$paramstr} -P {$savedir} {$url}");


    }

    /**
    * Creates a object containing all evidence pertaining to the portfolio with the given id
    *
    * @param string $command the command that will be run on the system e.g. wget with params
    * @return 0 if capture failed, 1 if capture completed, 2 if capture exceed download limit
    **/
    function process_command($command) {

        $ret = 0;
        $error = '';
        $first_line = true;

        $descriptorspec = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
            //2 => array('file', 'c:\temp\capturetest\err.txt', 'a')
        );

        //escape the command to stop any dubious operation that may be sent
        //$escaped_command = escapeshellcmd($command);

        $resource = proc_open($command, $descriptorspec, $pipes, null, $_ENV);

        if (is_resource($resource)) {
            $i = 0;
            while(!feof($pipes[2])) {
                $line = fgets($pipes[2], 1024);

                if($i == 2) {
                    $indexpage = explode('"', $line);
                    $indexpage = (isset($indexpage[1])) ? explode('"', $indexpage[1]) : explode('"', $indexpage[0]);
                    $firstpagepath = (is_array($indexpage)) ? pathinfo($indexpage[0]) : null;
                    $this->first_page = $firstpagepath['basename'];
                    $dirarr = explode("/", $firstpagepath['dirname']); //possibly \ if on windows based system
                    $this->save_dir = (is_array($dirarr)) ? end($dirarr) : null;
                }
                if (stristr($line, "FINISHED")) $ret = 1;// "Download completed successfully";
                if (stristr($line, "EXCEEDED")) $ret = 2;// "Download exceeded quota";
                $i++;
            }
            $exit_code = proc_close($resource);

            return $ret;

        }

        return $ret;

    }

    /**
     * Gets the first page property of this object
     *
     * @return string the filename of the first page
     */
    function return_first_page() {
        return $this->first_page;
    }

    /**
     * Gets the full save path property of this object
     *
     * @return string
     */
    function return_save_path() {
        return $this->save_path;
    }

    /**
     * Gets the save directory property of this object
     *
     * @return string the name of the directory
     */
    function return_save_dir() {
        return $this->save_dir;
    }

}

?>