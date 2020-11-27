<!--
 (C) 2016 Weblocks project.
 This software is released under the GPL, see LICENSE.
 https://opensource.org/licenses/gpl-license.php
-->
<p>
{% if session.has('user_name') %}
  Login User name = {{ session.get('user_name') }}
{% else %}
  Logout
{% endif %}
</p>
