<?php
/**
 * (C) 2016 Weblocks project.
 * This software is released under the GPL, see LICENSE.
 * https://opensource.org/licenses/gpl-license.php
 */
declare(strict_types = 1);

class LogoutController extends Phalcon\Mvc\Controller
{
    public function indexAction()
    {
        $this->session->remove('user_name');
        $this->response->redirect('');
    }
}
