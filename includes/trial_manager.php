<?php
class TrialManager {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Start a new trial for a user
     */
    public function startTrial($admin_id) {
        $trial_start = date('Y-m-d H:i:s');
        $trial_end = date('Y-m-d H:i:s', strtotime('+14 days'));
        
        $stmt = $this->pdo->prepare("
            UPDATE admins 
            SET trial_start_date = ?, 
                trial_end_date = ?, 
                is_trial_active = TRUE, 
                subscription_status = 'trial'
            WHERE id = ?
        ");
        
        return $stmt->execute([$trial_start, $trial_end, $admin_id]);
    }
    
    /**
     * Check if user's trial is active
     */
    public function isTrialActive($admin_id) {
        $stmt = $this->pdo->prepare("
            SELECT trial_end_date, is_trial_active, subscription_status 
            FROM admins 
            WHERE id = ?
        ");
        $stmt->execute([$admin_id]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return false;
        }
        
        // Check if trial has expired
        if (strtotime($result['trial_end_date']) < time()) {
            $this->expireTrial($admin_id);
            return false;
        }
        
        return $result['is_trial_active'] && $result['subscription_status'] === 'trial';
    }
    
    /**
     * Get trial information for a user
     */
    public function getTrialInfo($admin_id) {
        $stmt = $this->pdo->prepare("
            SELECT trial_start_date, trial_end_date, is_trial_active, subscription_status 
            FROM admins 
            WHERE id = ?
        ");
        $stmt->execute([$admin_id]);
        $result = $stmt->fetch();
        
        if (!$result) {
            return null;
        }
        
        $trial_end = strtotime($result['trial_end_date']);
        $now = time();
        $days_left = max(0, ceil(($trial_end - $now) / (24 * 60 * 60)));
        
        return [
            'start_date' => $result['trial_start_date'],
            'end_date' => $result['trial_end_date'],
            'is_active' => $result['is_trial_active'],
            'status' => $result['subscription_status'],
            'days_left' => $days_left,
            'is_expired' => $trial_end < $now
        ];
    }
    
    /**
     * Expire a trial
     */
    public function expireTrial($admin_id) {
        $stmt = $this->pdo->prepare("
            UPDATE admins 
            SET is_trial_active = FALSE, 
                subscription_status = 'expired'
            WHERE id = ?
        ");
        
        return $stmt->execute([$admin_id]);
    }
    
    /**
     * Get trial days remaining
     */
    public function getDaysRemaining($admin_id) {
        $trial_info = $this->getTrialInfo($admin_id);
        return $trial_info ? $trial_info['days_left'] : 0;
    }
}
?> 