Feature: I need to be able to load legacy users

    Scenario: Users must be imported when I execute user loader
        When I execute user loader
        Then users must be imported

    Scenario: No users must be imported when I execute user loader without data
        When I execute user loader without data
        Then no users must be imported

    Scenario: I can execute a loader without alias
        When I execute foo loader
        Then foo loader must have been executed
