<?php
namespace mozzler\auth\yii\oauth\auth;

use yii\filters\auth\HttpBearerAuth as HttpBearerAuthBase;

class HttpBearerAuth extends HttpBearerAuthBase {
	
	public function handleFailure($response) {
		// do nothing -- rappsio permission system will kick in using a null identity
    }
	
}
	
?>