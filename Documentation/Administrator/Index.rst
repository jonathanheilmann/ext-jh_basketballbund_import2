.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _admin-manual:

Administrator Manual
====================

.. _admin-installation:

Installation
------------

To install the extension, perform the following steps:

#. Go to the Extension Manager
#. Install the extension
#. Load the static template
#. Use the Constant Editor to setup jh_basketballbund_import2 (see :ref:`configuration-typoscript` for more information)


.. _admin-add-a-team:

Add a team
----------

* Go to List-Module and open your Storage Folder or any other folder/page where you want to organize your teams

* Select **Create new record**

* Select **Team** in section *basketball-bund.net - import 2*

* Set **Team**

* Set **League**:

  * Visit basketball-bund.net

  * Select the league or state your team participates in

  * Seach for your team and open the table

  * Copy liga_id from website address (example: copy **11153** from url *www.basketball-bund.net/index.jsp?Action=102&liga_id=1153*

* Save record


.. _admin-add-a-teamcollection:

Add a teamcollection
--------------------

* Go to List-Module and open your Storage Folder or any other folder/page where you organize your teams

* Select **Create new record**

* Select **Teamcollection** in section *basketball-bund.net - import 2*

* Set **Tilte** of teamcollection

* Select the **Teams** to be displayed in collection

* Save record


.. _admin-clear-cache:

Clear cache
-----------

The data imported by the extension are cached (for the periode set up in Constant Editor, see :ref:`configuration-typoscript`) in an own cache-table. To clear the cached data and force re-importing the data klick at the **Clear Cache** flash in the upper right corner and select **basketball-bund.net**.