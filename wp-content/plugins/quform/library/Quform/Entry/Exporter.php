<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_Exporter
{
    /**
     * @var Quform_Repository
     */
    protected $repository;

    /**
     * @var Quform_Form_Factory
     */
    protected $factory;

    /**
     * @var Quform_Options
     */
    protected $options;

    /**
     * @param  Quform_Repository    $repository
     * @param  Quform_Form_Factory  $factory
     * @param  Quform_Options       $options
     */
    public function __construct(Quform_Repository $repository, Quform_Form_Factory $factory, Quform_Options $options)
    {
        $this->repository = $repository;
        $this->factory = $factory;
        $this->options = $options;
    }

    /**
     * Handle the Ajax request to get the field choices list when exporting entries
     */
    public function getExportFieldList()
    {
        $formId = isset($_POST['form_id']) ? (int) $_POST['form_id'] : 0;

        if ($formId == 0) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        $config = $this->repository->getConfig($formId);

        if ($config === null) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Form not found', 'quform')
            ));
        }

        $form = $this->factory->create($config);

        $fieldList = array();

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element->config('saveToDatabase')) {
                $fieldList[] = array(
                    'label' => $element->getAdminLabel(),
                    'identifier' => $element->getIdentifier(),
                    'value' => 'element_' . $element->getId()
                );
            }
        }

        foreach ($this->getCoreEntryColumns() as $key => $label) {
            $fieldList[] = array(
                'value' => $key,
                'label' => $label
            );
        }

        $fieldList = $this->sortFieldList($fieldList, $form->getId());

        $fieldList = apply_filters('quform_export_field_list_' . $form->getId(), $fieldList);

        wp_send_json(array(
            'type' => 'success',
            'data' => $fieldList
        ));
    }

    /**
     * Sort the field list if the user has previously sorted it
     *
     * @param   array  $fieldList  The current field list
     * @param   int    $formId     The form ID
     * @return  array              The sorted field list
     */
    protected function sortFieldList(array $fieldList, $formId)
    {
        $map = get_user_meta(get_current_user_id(), 'quform_export_field_list_map', true);

        if ( ! is_array($map) || ! isset($map[$formId])) {
            return $fieldList;
        }

        $fields = array_reverse($map[$formId]);

        foreach ($fields as $field) {
            foreach ($fieldList as $key => $fieldListItem) {
                if (isset($fieldListItem['value']) && $field == $fieldListItem['value']) {
                    $swap = array_splice($fieldList, $key, 1); // Pluck the item from the field list
                    array_splice($fieldList, 0, 0, $swap); // Prepend the item to the start of the array
                }
            }
        }

        return $fieldList;
    }

    /**
     * Handle the Ajax request to save the field choices list when they are reordered
     */
    public function saveExportFieldListOrder()
    {
        $formId = isset($_POST['form_id']) ? (int) $_POST['form_id'] : 0;
        $fields = isset($_POST['fields']) && is_string($_POST['fields']) ? json_decode(stripslashes($_POST['fields']), true) : null;

        if ($formId == 0 || ! is_array($fields)) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Bad request', 'quform')
            ));
        }

        if ( ! current_user_can('quform_export_entries')) {
            wp_send_json(array(
                'type' => 'error',
                'message' => __('Insufficient permissions', 'quform')
            ));
        }

        $map = get_user_meta(get_current_user_id(), 'quform_export_field_list_map', true);

        if ( ! is_array($map)) {
            $map = array();
        }

        $map[$formId] = $fields;

        update_user_meta(get_current_user_id(), 'quform_export_field_list_map', $map);

        wp_send_json(array('type' => 'success'));
    }

    /**
     * Get the list of default entry columns
     *
     * @return array
     */
    protected function getCoreEntryColumns()
    {
        return array(
            'id' => __('Entry ID', 'quform'),
            'ip' => __('IP address', 'quform'),
            'form_url' => __('Form URL', 'quform'),
            'referring_url' => __('Referring URL', 'quform'),
            'post_id' => __('Page', 'quform'),
            'created_by' => __('User', 'quform'),
            'created_at' => __('Date', 'quform'),
            'updated_at' => __('Last modified', 'quform')
        );
    }

    /**
     * Generate the file containing exported entries
     *
     * @param  Quform_Form  $form     The form to export entries from
     * @param  array        $columns  The selected columns for the export file
     * @param  array        $format   The options for the format of the export file
     * @param  string       $from     The date of the earliest entry in the format YYYY-MM-DD
     * @param  string       $to       The date of the latest entry in the format YYYY-MM-DD
     */
    public function generateExportFile(Quform_Form $form, array $columns = array(), $format = array(), $from = '', $to = '')
    {
        $entries = $this->repository->exportEntries($form, $from, $to);

        // Sanitize chosen columns
        $coreColumns = $this->getCoreEntryColumns();
        $cols = array();

        foreach ($columns as $col) {
            if (array_key_exists($col, $coreColumns)) {
                // It's a core column, get the label
                $cols[$col] = $coreColumns[$col];
            } elseif (preg_match('/element_(\d+)/', $col, $matches)) {
                // It's an element column, so get the element admin label
                $element = $form->getElementById((int) $matches[1]);
                $cols[$col] = $element instanceof Quform_Element ? $element->getAdminLabel() : '';
            }
        }

        if ( ! class_exists('PHPExcel')) {
            require_once QUFORM_LIBRARY_PATH . '/PHPExcel.php';
        }

        // Fix for zip extension missing
        if ( ! class_exists('ZipArchive')) {
            PHPExcel_Settings::setZipClass(PHPExcel_Settings::PCLZIP);
        }

        $phpExcel = new PHPExcel();
        $sheet = $phpExcel->getActiveSheet();

        $headingColumnCount = 0;
        foreach ($cols as $col) {
            $sheet->setCellValueByColumnAndRow($headingColumnCount++, 1, $col);
        }

        $rowCount = 2;

        // Write each entry
        if (is_array($entries)) {
            foreach ($entries as $entry) {
                $row = array();
                $columnCount = 0;

                foreach ($cols as $col => $label) {
                    $row[$col] = isset($entry[$col]) ? $entry[$col] : '';

                    if (strlen($row[$col]) && strpos($col, 'element_') !== false) {
                        $elementId = (int) str_replace('element_', '', $col);
                        $row[$col] = $form->setValueFromStorage($elementId, $row[$col])->getValueText($elementId);
                    }

                    // Format the date to include the WordPress Timezone offset
                    if ($col == 'created_at' || $col == 'updated_at') {
                        $row[$col] = $this->options->formatDate($row[$col]);
                    }

                    $sheet->setCellValueByColumnAndRow($columnCount, $rowCount, $row[$col]);
                    $columnCount++;
                }

                $rowCount++;
            }
        }

        switch (Quform::get($format, 'type')) {
            case 'csv':
            default:
                $contentType = 'text/csv';
                $extension = '.csv';
                $writer = new PHPExcel_Writer_CSV($phpExcel);
                $writer->setExcelCompatibility((bool) Quform::get($format, 'excelCompatibility', false));
                $writer->setDelimiter(Quform::get($format, 'delimiter', ','));
                $writer->setEnclosure(Quform::get($format, 'enclosure', '"'));
                $writer->setUseBOM((bool) Quform::get($format, 'useBom', false));
                $writer->setLineEnding(Quform::get($format, 'lineEndings', "\r\n"));
                break;
            case 'xls':
                $contentType = 'application/vnd.ms-excel';
                $extension = '.xls';
                $writer = new PHPExcel_Writer_Excel5($phpExcel);
                break;
            case 'xlsx':
                $contentType = 'application/vnd.ms-excel';
                $extension = '.xlsx';
                $writer = new PHPExcel_Writer_Excel2007($phpExcel);
                break;
            case 'ods':
                $contentType = 'application/vnd.oasis.opendocument.spreadsheet';
                $extension = '.ods';
                $writer = new PHPExcel_Writer_OpenDocument($phpExcel);
                break;
            case 'html':
                $contentType = 'text/html';
                $extension = '.html';
                $writer = new PHPExcel_Writer_HTML($phpExcel);
                break;
        }

        // Send headers
        header('Content-Type: ' . $contentType);
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($form->config('name')) . '-' . date('Y-m-d') . $extension . '"');
        header('Cache-Control: private, must-revalidate, max-age=0');

        // Send the file contents
        $writer->save('php://output');
        exit;
    }
}
