.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _configuration:

Configuration Reference
=======================

.. _configuration-typoscript:

TypoScript Reference
--------------------

Properties
^^^^^^^^^^

.. container:: ts-properties

	============================== ===================================== ====================
	Property                       Data type                             Default
	============================== ===================================== ====================
	settings.cachelifetimeSeconds_ :ref:`t3tsref:data-type-int`          7200
	settings.displayborder_        :ref:`t3tsref:data-type-boolean`      1
	settings.jQuery_               :ref:`t3tsref:data-type-string`       EXT:jh_basketballbund_import2/Resources/Public/JavaScript/jquery-1.11.1.min.js
	settings.stressteam_           :ref:`t3tsref:data-type-string`       SC Borchen
	============================== ===================================== ====================


Property details
^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


.. _ts-plugin-tx-jhbasketballbundimport2-settings-jQuery:

settings.jQuery
"""""""""""""""

plugin.tx_jhbasketballbundimport2.settings.jQuery = :ref:`t3tsref:data-type-string`

Path to jQuery. Empty to not include jQuery


.. _ts-plugin-tx-jhbasketballbundimport2-settings-cachelifetime:

settings.cachelifetime
""""""""""""""""""""""

plugin.tx_jhbasketballbundimport2.settings.cachelifetimeSeconds = :ref:`t3tsref:data-type-int`

Cache lifetime in seconds. After this period the data will be re-imported brim basketball-bund.net


.. _ts-plugin-tx-jhbasketballbundimport2-settings-displayborder:

settings.displayborder
""""""""""""""""""""""

plugin.tx_jhbasketballbundimport2.settings.displayborder = :ref:`t3tsref:data-type-boolean`

Display table-border


.. _ts-plugin-tx-jhbasketballbundimport2-settings-stressteam:

settings.stressteam
"""""""""""""""""""

plugin.tx_jhbasketballbundimport2.settings.stressteam = :ref:`t3tsref:data-type-string`

Team to be stressed in Team content and to be displayed in Teamcollection content
