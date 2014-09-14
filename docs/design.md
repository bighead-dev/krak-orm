# Orm Design

This ORM is heavily inspired by Doctrine ORM; however, it's a much simpler implementation that allows a lot of flexibility.

Much of this document is the framework for building each component of the code.

## ClassMeta

The class meta holds all of the metadata for a specific class that can be used by the Orm.

ClassMeta will handle the following:

- Keeps track of class fields' types and mapping to sql table fields.
- returns a reference to the hydrator and marshaler.

## Hydrators & Marshalers

A hydrator will fill an object from the result set.
A marshaler will serialize (marshal) an object into an array ready to be saved by the Driver.

Haven't figured out if these are going to be full on objects with an interface or just a function pointer.
