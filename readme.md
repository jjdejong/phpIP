# Introduction #

phpIP is a web tool for managing an IP rights portfolio, especially patents. It is intended to satisfy most needs of an IP law firm. The tool was designed to be flexible and simple to use. It is based on an Apache-MySQL-PHP framework.

There are many IP rights management tools out there. They are all proprietary and pretty expensive. It is not the cost, in fact, that led us to designing our own system, because the design resources we spent could equate to the cost of a couple of years license and maintenance fees of existing systems. We found that existing systems are overkill for our needs, because they are designed to satisfy the needs of a majority – hence they have more features than what each individual user needs, so they are very complex to use, yet not all specific needs of the individual user are satisfied. So the user needs to adapt to the system, whereas it should be the other way round.

Since we are patent attorneys and don't have resources for selling and maintaining our software, yet would like others to benefit from it, and hopefully contribute, we decided to open source it. This is an important step in reaching the goal of creating a tool adapted to the user's specific needs. We also designed phpIP to be extremely flexible, so that, hopefully, most users will be able to configure it (and not redesign it) to fit their needs.

Head for the [Wiki](https://github.com/jjdejong/phpip/wiki) for further information.

# New features

## 2024-01-05 A significant upgrade of the autocompletion functionality

Navigation and selection in the suggestion lists can now be performed with the keyboard.

More foolproof.

Many bugfixes.

## 2023-11-16 A significant upgrade of the back-end and front-end infrastructures

Upgraded to Laravel 10 for the back-end.

Upgraded to Bootstrap 5 for the front-end.

Removed all dependencies to jQuery by rewriting many functions that depended on it, especially the autocompletion functionality.

## 2023-02-09 Automatic family import from Open Patent Services (OPS)
 
OPS provide a REST API for accessing world-wide patent information. We have integrated this service to automatically import a whole patent family into phpIP by just providing one of the publication numbers in the family.

The tool is available through the menu `Matters->Create family from OPS`
 
Use with caution, as we have not tested all the possible complex cases. Check in particular the links between multiple filings in the same country (divisions, continuations, internal priorities).
 
Sometimes you need to be patient after pressing "Create", and sometimes a time-out is reached, whereby you need to try again.
 
The format of the publication number must be respected. You can choose any number in the family, so you might as well choose one that is consistent (for instance the EP number if available).

What is actually imported for each family member:
* Country
* Filing information (date, number)
* Publication information
* Grant information
* Priority information (earliest priority)
* The English title
* Links between patents (PCT national phases, divisionals, continuations...)
* Applicants and inventors (if they're not present in the actors table, they will be created)
 
European validations are not imported. They are not (consistently) available in OPS (or I have not found how to access them...).
 
Importing _applicants and inventors_ is complicated and may be subject to duplicates, because this requires the management of their presence or their insertion in the actors table, with spellings that may vary. For best results, use the "typical" naming convention of the EPO, i.e., "NAME, FIRST NAME", and use only the "Name" field of the actor table (the "First Name" field is ignored, so leave it blank or use it at your convenience for differentiating similar names). The identified actors are indicated in the "notes" of the first patent for checking. To use more consistent data, these are imported from the EP case in the family - if there is no EP case available, they will be absent.

The tool can be used to complete an existing family, sparing you the effort of entering missing applications manually. For this operation, do not select the proposed case reference in the creation form, but force it to the value of the existing family.
 
**To use the tool, you must first create an account and a pair of application keys on the OPS site:**
 
https://developers.epo.org
 
Once your account is created and connected, go to "My Apps" on the top right. Create a "phpip" App and provide the generated keys in the .env file:

```
OPS_APP_KEY=<Consumer Key>
OPS_SECRET=<Consumer Secret Key>
```

## 2021-01-08 Document drag-and-drop merge functionality

Use your favorite DOCX templates to merge them with the data of a matter displayed in phpIP by simple drag-and-drop.

Check the dedicated [Wiki section](https://github.com/jjdejong/phpip/wiki/Templates-(email-and-documents)#document-template-usage)

## 2019-12-08 Renewal process management tool

This tool manages renewal watching, first calls, reminders, payments and invoicing of renewals. Emails are created for each step for a client's portfolio. The emails may be sent automatically or to oneself as a template for resending.

Check the dedicated [Wiki article](https://github.com/jjdejong/phpip/wiki/Renewal-Management).
