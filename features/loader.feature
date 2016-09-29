Feature: I need to be able to load legacy users

    Scenario: Users must be imported when I execute user loader
        When I execute user loader
        Then users must be imported

    Scenario: No users must be imported when I execute user loader without data
        When I execute user loader without data
        Then no users must be imported
