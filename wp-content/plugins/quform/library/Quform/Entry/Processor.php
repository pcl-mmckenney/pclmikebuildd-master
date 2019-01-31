<?php

/**
 * @copyright Copyright (c) 2009-2018 ThemeCatcher (https://www.themecatcher.net)
 */
class Quform_Entry_Processor extends Quform_Form_Processor
{
    /**
     * Process the given form
     *
     * @param   Quform_Form  $form  The form to process
     * @return  array               The result array
     */
    public function process(Quform_Form $form)
    {
        // Strip slashes from the submitted data (WP adds them automatically)
        $_POST = wp_unslash($_POST);

        // Pre-process hooks
        $result = apply_filters('quform_entry_pre_process', array(), $form);
        $result = apply_filters('quform_entry_pre_process_' . $form->getId(), $result, $form);

        if (is_array($result) && ! empty($result)) {
            return $result;
        }

        $this->uploader->mergeSessionFiles($form);

        $form->setValues($_POST, true);

        // Calculate which elements are hidden by conditional logic and which groups are empty
        $form->calculateElementVisibility();

        list($valid) = $form->isValid();

        if ($valid) {
            // Save the entry
            $entryId = $this->saveEntry($form);
            $form->setEntryId($entryId);

            // Process any uploads
            $this->uploader->process($form);

            // Save the entry data
            $this->saveEntryData($entryId, $form);

            // Post-process hooks
            $result = apply_filters('quform_entry_post_process', array(), $form);
            $result = apply_filters('quform_entry_post_process_' . $form->getId(), $result, $form);

            if (is_array($result) && ! empty($result)) {
                return $result;
            }

            return array(
                'type' => 'success',
                'data' => array('id' => $entryId),
                'message' => __('Entry saved', 'quform')
            );
        }

        return array(
            'type' => 'error',
            'errors' => $form->getErrors()
        );
    }

    /**
     * Save the entry and return the entry ID
     *
     * @param   Quform_Form  $form
     * @return  int
     */
    protected function saveEntry(Quform_Form $form)
    {
        $currentTime = current_time('mysql', true);
        $createdAt = Quform::get($_POST, 'entry_created_at') ? date('Y-m-d H:i:s', strtotime(Quform::get($_POST, 'entry_created_at'))) : $currentTime;

        $entry = array(
            'form_id'       => $form->getId(),
            'ip'            => Quform::substr(Quform::get($_POST, 'entry_ip'), 0, 45),
            'form_url'      => Quform::substr(Quform::get($_POST, 'entry_form_url'), 0, 512),
            'referring_url' => Quform::substr(Quform::get($_POST, 'entry_referring_url'), 0, 512),
            'post_id'       => is_numeric($postId = Quform::get($_POST, 'entry_post_id')) && $postId > 0 ? (int) $postId : null,
            'created_by'    => is_numeric($createdBy = Quform::get($_POST, 'entry_created_by')) && $createdBy > 0 ? (int) $createdBy : null,
            'created_at'    => $createdAt,
            'updated_at'    => $currentTime
        );

        $entry = $this->repository->saveEntry($entry, $form->getEntryId());

        return $entry['id'];
    }

    /**
     * Save the entry data
     *
     * @param  int          $entryId
     * @param  Quform_Form  $form
     */
    protected function saveEntryData($entryId, Quform_Form $form)
    {
        if ( ! ($entryId > 0)) {
            return;
        }

        $data = array();

        foreach ($form->getRecursiveIterator() as $element) {
            if ($element instanceof Quform_Element_Editable && $element->config('saveToDatabase') && ! $element->isConditionallyHidden()) {
                $data[$element->getId()] = $element->getValueForStorage();
            }
        }

        if (count($data)) {
            $this->repository->saveEntryData($entryId, $data);
        }
    }
}
