<?php
	class UserHelper extends Helper {
		function render($users) {
			$html = '';
			foreach($users as $user) {
				$html .= "<option value='{$user['id']}'>{$user['meno']}</option>\n";
			}
			return $html;
		}
	}
?>