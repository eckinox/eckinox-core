<?php

namespace Eckinox;

class Git {
    use singleton;

    protected $support_exec;

    public function __construct() {
        $this->support_exec = ! in_array('exec', explode(',', ini_get('disable_functions')));
    }

    public function getCommit($short = true) {
        if ($this->support_exec) {
            $short = $short ? 'h' : 'H';
            return trim(`git log --pretty="%$short" -n1 HEAD`);
        }

        return substr($this->_file_from_folder("refs/heads/" . $this->getBranch()), 0, 7);
    }

    public function getBranch() {
        if ($this->support_exec) {
            return trim(`git branch | grep \*`, "*\ \n");
        }

        return array_pop(explode('/', $this->_file_from_folder("HEAD")));
    }

    public function getOrigin($full_list = false) {
        return trim(! $full_list ? `git config --get remote.origin.url` : `git remote show origin`);
    }

    public function getRemoteName() {
        $origin = static::getOrigin();

        # We have an SSH connection as default remote
        if ( strpos($origin, "http://") === false ) {
            return substr($origin, strpos($origin, ':') + 1, -strlen('.git'));
        }
        else {
            # return parse_url($origin,  PHP_URL_PATH);
        }

        return false;
    }


    public function getCommitDate($format = "%F") {
        $commit = static::getCommit();
        $time = trim(`git show -s --format=%ct $commit`);

        return strftime($format, $time);
    }

    public function getUpdateCount($origin = null, $branch = null) {
        $origin ?? $origin = 'origin';
        $branch ?? $branch = static::getBranch();

        return trim(`git rev-list HEAD...$origin/$branch --count`);
    }

    public function getFileChanged() {
        return trim(`git status --porcelain`);
    }

    public function pull($origin = null, $branch = null) {
        $origin ?? $origin = 'origin';
        $branch ?? $branch = static::getBranch();

        return trim(`git pull $origin $branch`);
    }

    protected function _file_from_folder($file) {
        return trim(file_get_contents(SRC_DIR.".git/$file") ?: "");
    }
}
