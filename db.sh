#!/bin/bash

# Drop the database (if it exists)
echo "Dropping the database..."
bin/console d:d:d --force

# Create the database
echo "Creating the database..."
bin/console d:d:c

# Run the migrations
echo "Running migrations..."
echo "yes" | bin/console d:m:m

# Load the fixtures
echo "Loading fixtures for the test environment..."
echo "yes" | bin/console d:f:l -e test

# Run PHPUnit functional tests
echo "Running functional tests..."
bin/phpunit tests/Functional/
