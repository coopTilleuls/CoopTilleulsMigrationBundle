Feature: I need to be able to synchronize data in legacy database

    Scenario: User must be created in legacy database when I create it in local
        When I create a user
        Then user must be created in legacy database

    Scenario: User must be updated in legacy database when I update it in local
        When I update a user
        Then user must be updated in legacy database

    Scenario: User must be deleted in legacy database when I delete it in local
        When I delete a user
        Then user must be deleted from legacy database
