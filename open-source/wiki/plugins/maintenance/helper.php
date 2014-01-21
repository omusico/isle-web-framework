<?php
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

class helper_plugin_maintenance extends DokuWiki_Plugin {

    function __construct() {
        global $conf;
        $this->temp_dir = $conf['tmpdir'].'/maintenance';
        $this->script_last_log_file = $this->temp_dir.'/last.log';
        $this->script_last_pid_file = $this->temp_dir.'/last.pid';
        $this->script_last_script_file = $this->temp_dir.'/last.script';
        $this->manual_lock_file = $this->temp_dir.'/.lock';
        // put environmental variables to make shell scripts work right
        $user = posix_getpwuid(posix_geteuid());
        putenv('HOME='.$user['dir']);
        putenv('USER='.$user['name']);
        putenv('LANG='."en_US.UTF-8");
    }

    /**
     * Gets the configured script
     */
    function get_script() {
        $script = $this->getConf('script');
        $script = str_replace( 
            array('%dokuwiki%', '%bin%'), 
            array(substr(DOKU_INC, 0, -1), dirname(__FILE__).'/bin'),
            $script );
        return $script;
    }

    /**
     * Checks whether the site is currently locked
     * @return  integer  1: locked, 0: not locked
     */
    function is_locked() {
        $locks = glob($this->temp_dir.'/*.lock');
        if (!empty($locks)) return 1;
        if (is_file($this->temp_dir.'/.lock')) return 1;
        return 0;
    }

    /**
     * Runs a script and locks the site during running
     *
     * @return  integer  2: already run, 1: success, 0: fail
     */
    function script_start($script) {
        $script_hash = sha1($script);
        $lockfile = $this->temp_dir.'/'.$script_hash.'.lock';
        @io_mkdir_p(dirname($lockfile));
        $fh = fopen($lockfile, 'wb');
        if (flock($fh, LOCK_EX | LOCK_NB)) {
            $cmd = 'nohup bash '.escapeshellarg($script).' > '.escapeshellarg($this->script_last_log_file). ' 2>&1 & echo $!';
            exec($cmd, $output, $result);
            if ($result != 0) return 0;
            file_put_contents($this->script_last_pid_file, $output[0]);
            file_put_contents($this->script_last_script_file, $script_hash);
            return 1;
        }
        return 2;
    }

    /**
     * Kills the last started script
     *
     * @return  integer  2: already not run, 1: success, 0: fail
     */
    function script_stop() {
        $script_hash = trim(file_get_contents($this->script_last_script_file));
        $lockfile = $this->temp_dir.'/'.$script_hash.'.lock';
        $result = $this->script_updatelock($lockfile);
        if ($result != 1) return 2;
        $pid = trim(file_get_contents($this->script_last_pid_file));
        $cmd = "ps p $pid >&-";
        exec($cmd, $output, $result);
        if ($result != 0) return 2;
        $cmd = "kill -9 $pid";
        exec($cmd, $output, $result);
        if ($result != 0) return 0;
        return 1;
    }

    /**
     * Checks whether it's time to run
     */
    function script_autocheck() {
        $file = $this->script_last_pid_file;
        $last_run = (is_file($file)) ? filemtime($file) : 0;
        $interval = $this->getConf('script_auto_interval');
        $now = time();
        if ($now > $last_run+$interval) return true;
        return false;
    }

    /**
     * Checks all script locks
     */
    function script_updatelockall() {
        // glob doesn't match hidden (started with ".") files
        $locks = glob($this->temp_dir.'/*.lock');
        foreach ($locks as $lock) {
            $this->script_updatelock($lock);
        }
    }

    /**
     * Checks a script lock and removes it if already terminated
     *
     * @return  integer  3: already no lock, 2: terminated and removed, 1: not terminated, 0: terminated and failed to remove
     */
    function script_updatelock($lockfile) {
        if (!is_file($lockfile)) return 3;
        $fh = fopen($lockfile, 'wb');
        if (flock($fh, LOCK_EX | LOCK_NB)) {
            @flock($fh, LOCK_UN);
            @unlink($lockfile);
            if (!is_file($lockfile)) return 2;
            return 0;
        }
        return 1;
    }

    /**
     * Locks the site manually, must unlock manually
     *
     * @return  integer  2: already locked, 1: success, 0: fail
     */
    function manual_lock() {
        if (is_file($this->manual_lock_file)) return 2;
        @io_mkdir_p(dirname($this->manual_lock_file));
        @touch($this->manual_lock_file);
        if (is_file($this->manual_lock_file)) return 1;
        return 0;
    }

    /**
     * Removes a manual lock
     *
     * @return  integer  2: already no lock, 1: success, 0: fail
     */
    function manual_unlock() {
        if (!is_file($this->manual_lock_file)) return 2;
        @unlink($this->manual_lock_file);
        if (!is_file($this->manual_lock_file)) return 1;
        return 0;
    }

}
