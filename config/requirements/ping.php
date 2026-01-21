<?php
require_once 'config.php';
echo getDB() ? "✅ Main DB Connected<br>" : "❌ Main DB Failed<br>";
echo getCampaignDB() ? "✅ Campaign DB Connected" : "❌ Campaign DB Failed";
?>