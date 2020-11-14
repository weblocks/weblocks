User List<br>
{% for user in users %}
  {{ user.id }} / {{ user.name }} / {{ user.password }} / {{ user.role }}<br>
{% endfor %}
