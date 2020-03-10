<?php
namespace OCA\Files_MindMap\Migration;

use OCP\Migration\IOutput;
use OCP\Migration\IRepairStep;
use OCP\ILogger;
use OC\Core\Command\Maintenance\Mimetype\UpdateJS;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

class UninstallStep implements IRepairStep {

    /** @var ILogger */
    protected $logger;
    protected $updateJS;

    public function __construct(ILogger $logger, UpdateJS $updateJS) {
            $this->logger = $logger;
            $this->updateJS = $updateJS;
    }

    /**
    * Returns the step's name
    */
    public function getName() {
            return 'Uninstall MindMap!';
    }

    /**
    * @param IOutput $output
    */
    public function run(IOutput $output) {
        $configDir = \OC::$configDir;
        $mimetypealiasesFile = $configDir . 'mimetypealiases.json';
        $mimetypemappingFile = $configDir . 'mimetypemapping.json';

        $this->removeFromFile($mimetypealiasesFile, ['application/km' => 'mindmap']);
        $this->removeFromFile($mimetypemappingFile, ['km' => 'application/km']);
        $this->logger->info("Remove .km from mimetype list.", ["app" => "files_mindmap"]);
        $this->updateJS->run(new StringInput(''), new NullOutput());

        $this->logger->info("Remove mindmap icon from core/img directory.", ["app" => "files_mindmap"]);
        unlink(\OC::$SERVERROOT . '/core/img/filetypes/mindmap.svg');
    }

    private function removeFromFile(string $filename, array $data) {
        $obj = [];
        if (file_exists($filename)) {
            $content = file_get_contents($filename);
            $obj = json_decode($content, true);
        }
        foreach ($data as $key => $value) {
            unset($obj[$key]);
        }
        file_put_contents($filename, json_encode($obj,  JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
    }
}
