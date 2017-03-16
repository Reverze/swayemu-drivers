# Package info
This packages comes with tools which emulates behaviour of swayengine's drivers.

# Usage
Before use, you must start virtual memria kernel. This packages
comes with class MKernel which emulates the most primary
memria kernel behaviour.

```php
$mkernel = new \MKernel(__DIR__ . '/kernel_virt');
```

This will create simple directory structure for needed
for drivers.
