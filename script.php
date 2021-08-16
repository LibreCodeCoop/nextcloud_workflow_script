<?php

use GO\Scheduler;

require_once 'vendor/autoload.php';

require_once $argv[3] . '/lib/base.php';
$config = \OC::$server->get(\OCP\IConfig::class);
$scheduled = json_decode($config->getAppValue($argv[2], 'jobs', '[]'), true);

switch($argv[1]) {
    case 'schedule':
        $scheduled[] = $argv[4];
        $scheduled = array_unique($scheduled);
        $config->setAppValue($argv[2], 'jobs', json_encode($scheduled));
        break;
    case 'run':
        $scheduler = new Scheduler();
        $runTime = new DateTime('now');
        $settings = $config->getSystemValue($argv[2], []);
        foreach ($scheduled as $key => $file) {
            $date = pathinfo($file, PATHINFO_FILENAME);
            $file = escapeshellarg($argv[3] . '/data/' . $file);
            $job = $scheduler->raw(
                'ffmpeg',
                [
                    '-re' => null,
                    '-i' => $argv[3] . '/data/' . $file,
                    '-c:v' => 'copy',
                    '-b:v' => '6000k',
                    '-bufsize' => '6000k',
                    '-c:a' => 'copy',
                    '-flags' => '+global_header',
                    '-f' => 'flv',
                    '-flvflags' => 'no_duration_filesize',
                    $settings['rtmp_url'] => null
                ]
            )
            ->date(DateTime::createFromFormat('Ymd_Hi', $date));
            if ($job->isDue($runTime)) {
                unset($scheduled[$key]);
            }
        }
        $config->setAppValue($argv[2], 'jobs', json_encode($scheduled));
        $scheduler->run();
        break;
}
