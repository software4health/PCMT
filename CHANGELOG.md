VER 1.1.3 / Unreleased
==================

**Added**
* [#380](https://gitlab.com/pcmt/pcmt/-/issues/380): User get information about parallel changes in the same draft when trying to save outdated draft. 
* [#113](https://gitlab.com/pcmt/pcmt/-/issues/113): Concatenated Attribute are available for many attributes.
* [#334](https://gitlab.com/pcmt/pcmt/-/issues/334): List of GS1 ISO 3166 are available as reference data.
* [#361](https://gitlab.com/pcmt/pcmt/-/issues/361): Integration test working with Selenium are available by new command (dev-test-selenium).
* [#302](https://gitlab.com/pcmt/pcmt/-/issues/302): Functional testing using Behat are available by new command (dev-test-api).
* [#351](https://gitlab.com/pcmt/pcmt/-/issues/351): Mutation tests are available by a new command (dev-test-mutation).
* [#328](https://gitlab.com/pcmt/pcmt/-/issues/328): French language is available in the user interface. 
* [#236](https://gitlab.com/pcmt/pcmt/-/issues/236): Pipeline fails when code coverage on branch is below threshold set in gitlab.
* [#236](https://gitlab.com/pcmt/pcmt/-/issues/236): Phpunit tests code coverage report are automatically generated.

**Changed**
* [#385](https://gitlab.com/pcmt/pcmt/-/issues/385): Change GDSN language attributes to use language code reference data
* [#384](https://gitlab.com/pcmt/pcmt/-/issues/384): Add Language Codes (ISO-639) to Reference Data pull
* [#277](https://gitlab.com/pcmt/pcmt/-/issues/277): GDSN-Queue (e2open) Import goes through Drafts/Approvals.
* [#314](https://gitlab.com/pcmt/pcmt/-/issues/314): Messages when draft is already approved or rejected.


**Removed**
* [#277](https://gitlab.com/pcmt/pcmt/-/issues/277): Dependencies in PcmtDraftBundle on PcmtCoreBundle - it can works separately.
* [#340](https://gitlab.com/pcmt/pcmt/-/issues/340): pcmt:custom-dataset:csv:create command.
* [#317](https://gitlab.com/pcmt/pcmt/-/issues/317): Connection between phpstorm and unit test code coverage report.

**Fixed**
* [#403](https://gitlab.com/pcmt/pcmt/-/issues/403): Only for the RH family it is possible to change the locale while editing the product
* [#224](https://gitlab.com/pcmt/pcmt/-/issues/224): Displaying "missing required attributes" in a draft for a product model isn't working properly
* [#350](https://gitlab.com/pcmt/pcmt/-/issues/350): TYPE SPECIFIC PARAMETERS are not saved in the Concatenated Attribute.
* [#353](https://gitlab.com/pcmt/pcmt/-/issues/353): Draft list disappears when an attribute from family of the draft is removed.
* [#196](https://gitlab.com/pcmt/pcmt/-/issues/196): User get unclear information about missing required field in a draft.
* [#346](https://gitlab.com/pcmt/pcmt/-/issues/346): User can't create a draft of a new product.
* [#330](https://gitlab.com/pcmt/pcmt/-/issues/330): Places and messages where there is no translation.
* [#316](https://gitlab.com/pcmt/pcmt/-/issues/316): GDSN Effective Date Time field not mapped
* [#317](https://gitlab.com/pcmt/pcmt/-/issues/317): 'make dev-clean' command is broken and doesn't work.
* [#305](https://gitlab.com/pcmt/pcmt/-/issues/305): Duplicated alert about validation violation when user try to approve wrong filled draft.
* [#297](https://gitlab.com/pcmt/pcmt/-/issues/297): ERD pipeline job is timing out.


VER 1.1.2 / 2020-02-25
==================

**Added**
* [#303](https://gitlab.com/pcmt/pcmt/-/issues/303): Shell script for load data from file into database (dev-import-sql).

**Changed**
* [#236](https://gitlab.com/pcmt/pcmt/-/issues/236): Exclude DependencyInjection and upgrades from code coverage report.

**Removed**
* none

**Fixed**
* [#298](https://gitlab.com/pcmt/pcmt/-/issues/298): No message to user when approving draft already approved by bulk approve.
* [#300](https://gitlab.com/pcmt/pcmt/-/issues/300): Bulk action show wrong number of selected drafts.



VER 1.1.1 / 2020-02-20
==================

**Added**
* [#272](https://gitlab.com/pcmt/pcmt/-/issues/272): Client messages improvement in the draft system.

**Changed**
* none

**Removed**
* none

**Fixed**
* [#294](https://gitlab.com/pcmt/pcmt/-/issues/294): Bulk approve doesn't work after using process of data restoration.
* [#290](https://gitlab.com/pcmt/pcmt/-/issues/290): User cannot approve a draft after adding a concatenated attribute to the family.