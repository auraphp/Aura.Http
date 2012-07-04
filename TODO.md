- finish tests for non-adapter classes
- figure out how to write tests for adapter classes
- revisit expireSessionCookies()
- update method names to standard verbiage (e.g. getAll())
- have cookie go with ($name, $value, array $other = []) to make simple
  name-value pairing easier?
- cookie::setFromString() becomes setFromHeader(), and move jar parsing into
  cookie class
