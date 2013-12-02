CHANGELOG
=========

DONE
----

* [2013-12-02] Avoid lost message when publishing to an unexisting exchange
  then to a valid exchange (used transactions)
* Replace "consume" by "get" to avoid closed connection (with configurable
  poll-interval)

TODO
----

* Handle PHP Catchable Fatal errors ?
* Add profiling
* Refactor SignalHandler
* Clean Manager::consume & Wrapper (use encapsulation)
