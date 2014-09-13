Krak ORM
========

Simple, fast, flexible DataMapper ORM.

## Components

Orm Drivers, Class Mapper, Hydrators, Marshalers.

Orm Drivers are the central entity that manage the models with class mapping, hydration, and marshaling.

The Class Mapper holds the metadata for a class for a specific driver.

Hydrators are responsible for filling up the model from the result set.

Marshalers are responsible for marshaling to be stored via the specific driver.

## Development Steps

- Build Class Meta
- Build Query
- Build Marshalers
- Build Hydrators
