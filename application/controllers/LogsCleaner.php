<?php

class LogsCleaner extends CI_Controller
{
    /**
     * List of tables with logs
     * @var array
     */
    private $tables = [
        'optiply_logs',
        'exact_logs',
        //'afas_logs',
    ];

    /**
     * Clear tables with log information. Logs for last 3 days will be left
     * Only for CLI
     */
    public function clearLogs() {
        if($this->input->is_cli_request()) {
            $delTime = date('Y-m-d H:i:s', strtotime('-3 days'));

            foreach ($this->tables as $table) {
	            if($table == 'optiply_log'){
		            $this->db->where('type !=', 'webhook_order');
	            }
                $this->db->delete($table, ['date <=' => $delTime]);
            }
        }
    }
}