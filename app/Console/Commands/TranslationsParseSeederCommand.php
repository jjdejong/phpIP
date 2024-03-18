<?php
// from https://dev.to/agenceappy/generating-po-files-with-laravel-translating-blade-templates-15im
// By Agence Appy
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class TranslationsParseSeederCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:parse-seeder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse seeder to find translations and insert it in lang files';

    /**
     * The functions used in your blade templates to translate strings
     *
     * @var string
     */
    protected $functions = ['singular' => '__'];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $files_list = [
            "ActorRoleTableSeeder.php" => ['name', 'notes'],
            "ActorTableSeeder.php"  => ['notes'],
            "ClassifierTypeTableSeeder.php"  => ['type'],
            "CountryTableSeeder.php"  => ['iso'],
            "EventNameTableSeeder.php"  => ['name', 'notes'],
            "MatterCategoryTableSeeder.php"  => ['category'],
            "MatterTypeTableSeeder.php"  => ['type'],
            "TaskRulesTableSeeder.php" => ['detail', 'notes'],
            "TemplateClassesTableSeeder.php" => ['notes']
        ];
        $translations = [];
        // Init translations global container
        $translations[$this->functions['singular']] = [];
        // Parse all directories within the resources/views folder
        foreach (array_keys($files_list) as $file):
            // Parse database files
            $file_path = database_path('seeders/' . $file);
            $lines = File::lines($file_path);

            // Parse all lines from file

            $keys = $files_list[$file];
            foreach ($lines as $index => $line):
                // Get strings from line
                $string = $this->parseFields($line, $keys);
                if ($string != ""){
                    if (isset($translations["__"][$string])) {
                        $translations["__"][$string][] = $file_path.':'.($index+1);
                    } else {
                        $translations["__"][$string] = [$file_path.':'.($index+1)];
                    }
                }
            endforeach;

            // Create directories if not exists
            $language_dir = lang_path('blade-translations');
            if (!File::isDirectory($language_dir)):
                File::makeDirectory($language_dir, 0755);
            endif;

            $language_dir_interface = lang_path('blade-translations');

            // Create file content
            $content = "<?php\n\n";
            $content .= "return [\n\n";
            $content .= "    /*\n";
            $content .= "    |--------------------------------------------------------------------------\n";
            $content .= "    | STATIC STRINGS TRANSLATIONS\n";
            $content .= "    |--------------------------------------------------------------------------\n";
            $content .= "    |\n";
            $content .= "    |  !!! WARNING - This is a file generated by the 'translations:parse-blade' command. !!!\n";
            $content .= "    |  You should not modify it, as it shall be replaced next time this command is executed.\n";
            $content .= "    |\n";
            $content .= "    */\n\n";

            $i = 0;
            foreach ($this->functions as $type => $strFunction):
                //foreach ($functions as $strFunction):
                    foreach ($translations[$strFunction] as $translation => $paths):
                        foreach ($paths as $p):
                            $content .= "    // $p \n";
                        endforeach;
                        $content .= "    $i => $strFunction($translation), \n";
                        $i++;
                    endforeach;
                //endforeach;
            endforeach;
            $content .= "\n];";

            // Generate file
            File::put($language_dir_interface.'/seeder.php', $content);

        endforeach;
        

        $this->info('Strings have been exported from seeders successfully !');
    }

    /**
     * Return field value within the line when key is in keys
     */
    private function parseFields($line, $keys) {
        $return = "";
        $pos = strpos($line, '=>');
        if ($pos !== false) {
            foreach($keys as $key) {
                $result =  $this->getNextString(trim($line), 0, 'singular');
                $string = substr($result['string'], 1, -1);
                if($string === $key) {
                    //echo ($result['line']."\n");
                    $result = $this->getNextString($result['line'], 3, 'singular');
                    if( $result) {
                        if (!is_numeric(substr($result['string'], 1, -1))){
                            $return = $result['string'];
                        }
                    }
                }
            }
        }
        return $return;
    }
    

    /**
     * Return first string found and the rest of the line to be parsed
     */
    private function getNextString($subline, $pos, $type) {

        $substr = trim(substr($subline, $pos));

        $separator = $substr[0];
        $nextSeparatorPos = $this->getNextSeparator($substr, $separator);
        if (!$nextSeparatorPos) return [];

        if ($type == 'singular'):
            $string = substr($substr, 0, $nextSeparatorPos+1);
            $rest = substr($substr, $nextSeparatorPos+1);

            // security check : string must start and end with the separator => same character
            if ($string[0] != $string[strlen($string)-1]):
                return [];
            endif;

            return ['string' => $string, 'line' => $rest];
        else:
            $first_string = substr($substr, 0, $nextSeparatorPos+1);
            $rest = substr($substr, $nextSeparatorPos+1);

            // security check : string must start and end with the separator => same character
            if ($first_string[0] != $first_string[strlen($first_string)-1]):
                return [];
            endif;

            $comma_pos = strpos($rest, ',');
            $rest = trim(substr($rest, $comma_pos+1));

            $separator = $substr[0];
            $nextSeparatorPos = $this->getNextSeparator($rest, $separator);

            if (!$nextSeparatorPos) return [];

            $second_string = substr($rest, 0, $nextSeparatorPos+1);
            $rest = substr($rest, $nextSeparatorPos+1);

            // security check : string must start and end with the separator => same character
            if ($second_string[0] != $second_string[strlen($second_string)-1]):
                return [];
            endif;

            return ['string' => [$first_string, $second_string], 'line' => $rest];
        endif;
    }

    /**
     * Return first unescaped separator of string
     */
    private function getNextSeparator($str, $separator) {
        $substr = substr($str, 1);
        $found = false;
        preg_match_all('/'.$separator.'/', $substr, $matches, PREG_OFFSET_CAPTURE);
        foreach($matches[0] as $match):
            $pos = $match[1];
            if ($substr[$pos-1] != '\\'):
                $found = true;
                break;
            endif;
        endforeach;

        if ($found) return $pos+1;
        return false;
    }
}
