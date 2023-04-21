<?php
  require_once('./RequirementsChecker.php');
  $checker = new RequirementsChecker(include('./requirements.php'));

  $requirements = $checker->check();
  $php = $checker->checkPHPversion();

  $dbTestMethods = [
      // Should be first as it's the most important for this test as all other tests are dropping the table
      'testDropTable',
      'testCreateTable',
      'testSelect',
      'testInsert',
      'testUpdate',
      'testDelete',
      'testAlter',
      'testIndex',
      'testReferences'
  ];

  $dbHasErrors = false;
  $dbValidationErrors = [];

  if (isset($_POST['databaseTest'])) {
      require_once('./DatabaseTest.php');

      foreach (['db_hostname', 'db_port', 'db_username', 'db_name'] as $attribute) {
          if (empty($_POST[$attribute])) {
              $dbValidationErrors[$attribute] = 'This field is required.';
          }
      }

      if (count($dbValidationErrors) === 0) {
          $db = new DatabaseTest(
              $_POST['db_hostname'],
              $_POST['db_port'],
              $_POST['db_username'],
              $_POST['db_password'] ?? null,
              $_POST['db_name'],
          );
          $db->connect();
      }
  }
  ?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Concord CRM Requirements Checker</title>
</head>

<body class="bg-gray-50">
    <div class="max-w-5xl mx-auto">
        <div class="px-8 py-12">
            <a href="https://www.concordcrm.com">
                <img src="https://www.concordcrm.com/images/logo/logo.png" class="mx-auto" />
            </a>

            <h1 class="text-2xl leading-10 font-bold mt-12 -mb-2 text-gray-700">
                Concord CRM Requirements Checker
            </h1>

            <p class="mb-5">
                <a class="text-indigo-600 hover:text-indigo-800" href="https://www.concordcrm.com/docs">
                    Documentation
                </a>
            </p>

            <?php if (isset($requirements['errors']) && $requirements['errors'] === true || $php['supported'] === false) { ?>
            <div class="text-red-500 p-4 bg-red-50 border border-red-200 mb-8 mt-3 rounded-md text-sm">
                <div class="flex items-center">
                    <div class="shrink-0">
                        <svg class="h-8 w-8 text-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-base font-semibold">
                            Some requirements are not met!
                        </h3>
                    </div>
                </div>
            </div>
            <?php } ?>
            <h4 class="text-lg mt-5 mb-3 text-gray-700 font-semibold">PHP Version</h4>
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow-sm overflow-hidden border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Required PHP Version
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Current
                                    </th>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            >= <?php echo $php['minimum']; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex <?php echo $php['supported'] ? 'text-green-600' : 'text-red-500'; ?>">
                                                <?php $php['supported'] && include('./passes-icon.php'); echo $php['current']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <h4 class="text-lg mb-3 mt-10 text-gray-700 font-semibold">Required PHP Extensions</h4>
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow-sm overflow-hidden border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Extension
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Enabled
                                    </th>
                                </thead>
                                <tbody>
                                    <?php foreach ($requirements['results']['php'] as $requirement => $enabled) { ?>
                                    <tr>
                                        <td
                                            class="px-6 py-4 bg-white whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $requirement; ?>
                                        </td>
                                        <td class="px-6 py-4 bg-white whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex <?php echo $enabled ? 'text-green-600' : 'text-red-500'; ?>">
                                                <?php $enabled && include('./passes-icon.php'); echo $enabled ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="text-lg mb-3 mt-10 text-gray-700 font-semibold">Required PHP Functions</h4>
            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow-sm overflow-hidden border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Function
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Enabled
                                    </th>
                                </thead>
                                <tbody>
                                    <?php foreach ($requirements['results']['functions'] as $function => $enabled) { ?>
                                    <tr>
                                        <td
                                            class="px-6 py-4 bg-white whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo $function; ?>
                                        </td>
                                        <td class="px-6 py-4 bg-white whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex <?php echo $enabled ? 'text-green-600' : 'text-red-500'; ?>">
                                                <?php $enabled && include('./passes-icon.php'); echo $enabled ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="text-lg -mb-1 mt-10 text-gray-700 font-semibold">Recommended PHP Extensions/Functions</h4>

            <p class="mb-3 text-gray-600 text-sm">
                If there are recommended requirements that are not passing, they won't prevent you from
                installing Concord CRM.
            </p>

            <div class="flex flex-col">
                <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
                        <div class="shadow-sm overflow-hidden border border-gray-200 sm:rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Requirement
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-sm font-medium text-gray-700">
                                        Enabled
                                    </th>
                                </thead>
                                <tbody>
                                    <?php foreach ($requirements['recommended']['php'] as $requirement => $enabled) { ?>
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-white">
                                            <?php echo $requirement; ?> <span class="text-gray-400 text-xs">(ext)</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 bg-white">
                                            <span
                                                class="inline-flex <?php echo $enabled ? 'text-green-600' : 'text-red-500'; ?>">
                                                <?php $enabled && include('./passes-icon.php'); echo $enabled ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    <?php foreach ($requirements['recommended']['functions'] as $function => $enabled) { ?>
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 bg-white">
                                            <?php echo $function; ?> <span class="text-gray-400 text-xs">(func)</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 bg-white">
                                            <span
                                                class="inline-flex <?php echo $enabled ? 'text-green-600' : 'text-red-500'; ?>">
                                                <?php $enabled && include('./passes-icon.php'); echo $enabled ? 'Yes' : 'No'; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <h4 class="text-lg mt-5 text-gray-700 font-semibold">Database
                <span class="text-sm text-gray-500 font-medium">recommended check</span>
            </h4>
            <?php if (! isset($db) || (isset($db) && ! $db->isConnected())) { ?>
            <p class="text-red-500 mb-5 text-sm">You must create MySQL database before testing.</p>
            <?php } ?>
            <?php
          if (isset($db)) {
              if (! $db->isConnected()) {
                  $dbHasErrors = true; ?>
            <p class="text-gray-800 font-bold mb-1">
                The following error occured while trying to establish connection with the database:
            </p>
            <p class="text-red-500 p-4 bg-red-50 border border-red-200 mb-5 mt-3 rounded-md">
                <?php echo $db->getConnectionError(); ?>
            </p>
            <?php
              } else {
                  if (! $db->testVersion('5.6')) {
                      $dbHasErrors = true; ?>
            <div class="text-red-500 p-4 bg-red-50 border border-red-200 mb-5 mt-3 rounded-md">
                Concord CRM requires at leat MySQL 5.6 version. Your servers uses MySQL:
                <?php echo $db->getVersion(); ?>
            </div>
            <?php
                  } else {
                      $hasTestError = false;
                      foreach ($dbTestMethods as $test) {
                          if (!$hasTestError) {
                              $db->{$test}();

                              if ($db->lastError()) {
                                  $hasTestError = true;
                                  $dbHasErrors = true;

                                  $errorMessage = '<p>'. $db->lastError() .'</p>';

                                  if (strpos($errorMessage, 'command denied')) {
                                      $errorMessage .= '<p class="mt-1">Make sure to give <span class="font-bold">all privileges to the MySQL user</span>, check the installation video in the documentation.</p>';
                                  }

                                  echo '<div class="text-red-500 p-4 bg-red-50 border border-red-200 mb-5 mt-3 rounded-md text-sm">'.$errorMessage.'</div>';
                              }
                          }
                      }
                  }
              }

            if (!$dbHasErrors) { ?>
            <div class="flex text-green-500 p-4 bg-green-50 border border-green-200 mb-5 mt-3 rounded-md">
                <?php include('passes-icon.php'); ?>
                <p class="ml-2">
                    Your database passes the tests, as a tip, if you are going to install Concord CRM, you can use the
                    same database, user and password since you already have them configured.
                </p>
            </div>
            <?php
              }
          }
          ?>
            <form method="POST" action="index.php">
                <input type="hidden" name="databaseTest" value="1" />
                <div class="bg-white p-7 shadow-sm border border-gray-200 sm:rounded-lg">
                    <div class="flex flex-col">
                        <div class="space-y-6 sm:space-y-5">
                            <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start">
                                <label for="inputDatabaseHostname"
                                    class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    <span class="text-red-600 text-sm mr-1">*</span>Hostname
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" value="<?php echo $_POST['db_hostname'] ?? 'localhost'; ?>"
                                        name="db_hostname"
                                        class="appearance-none outline-none bg-white text-sm block w-full rounded-md border-2 border-gray-300 py-2.5 px-3 shadow-sm focus:border-indigo-500"
                                        id="inputDatabaseHostname">
                                    <?php echo isset($dbValidationErrors['db_hostname']) ? '<p class="mt-2 text-sm text-red-600">'.$dbValidationErrors['db_hostname'].'</p>' : ''; ?>
                                </div>
                            </div>
                            <div
                                class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="inputDatabasePort"
                                    class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    <span class="text-red-600 text-sm mr-1">*</span>Port
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" value="<?php echo $_POST['db_port'] ?? '3306'; ?>" name="db_port"
                                        class="appearance-none outline-none bg-white text-sm block w-full rounded-md border-2 border-gray-300 py-2.5 px-3 shadow-sm focus:border-indigo-500"
                                        id="inputDatabasePort">
                                    <p class="mt-2 text-sm text-gray-500">* The default MySQL ports is 3306, change the
                                        value only if you are certain that you are using different port.</p>
                                    <?php echo isset($dbValidationErrors['db_port']) ? '<p class="mt-2 text-sm text-red-600">'.$dbValidationErrors['db_port'].'</p>' : ''; ?>
                                </div>
                            </div>
                            <div
                                class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="inputDatabaseName"
                                    class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    <span class="text-red-600 text-sm mr-1">*</span>Database Name
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" value="<?php echo $_POST['db_name'] ?? ''; ?>" name="db_name"
                                        class="appearance-none outline-none bg-white text-sm block w-full rounded-md border-2 border-gray-300 py-2.5 px-3 shadow-sm focus:border-indigo-500"
                                        id="inputDatabaseName">
                                    <p class="mt-2 text-sm text-gray-500">* Make sure that you have created the database
                                        before configuring.</p>
                                    <?php echo isset($dbValidationErrors['db_name']) ? '<p class="mt-2 text-sm text-red-600">'.$dbValidationErrors['db_name'].'</p>' : ''; ?>
                                </div>
                            </div>
                            <div
                                class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="inputDatabaseUsername"
                                    class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    <span class="text-red-600 text-sm mr-1">*</span>Database Username
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="text" value="<?php echo $_POST['db_username'] ?? ''; ?>"
                                        name="db_username"
                                        class="appearance-none outline-none bg-white text-sm block w-full rounded-md border-2 border-gray-300 py-2.5 px-3 shadow-sm focus:border-indigo-500"
                                        id="inputDatabaseUsername">
                                    <p class="mt-2 text-sm text-gray-500">* Make sure you have set ALL privileges for
                                        the user.</p>
                                    <?php echo isset($dbValidationErrors['db_username']) ? '<p class="mt-2 text-sm text-red-600">'.$dbValidationErrors['db_username'].'</p>' : ''; ?>
                                </div>
                            </div>
                            <div
                                class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:border-t sm:border-gray-200 sm:pt-5">
                                <label for="inputDatabasePassword"
                                    class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">
                                    Database Password
                                </label>
                                <div class="mt-1 sm:mt-0 sm:col-span-2">
                                    <input type="password" name="db_password"
                                        class="appearance-none outline-none bg-white text-sm block w-full rounded-md border-2 border-gray-300 py-2.5 px-3 shadow-sm focus:border-indigo-500"
                                        id="inputDatabasePassword">
                                    <p class="mt-2 text-sm text-gray-500">* Enter the database user password.</p>
                                    <?php echo isset($dbValidationErrors['db_password']) ? '<p class="mt-2 text-sm text-red-600">'.$dbValidationErrors['db_password'].'</p>' : ''; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit"
                            class="mt-10 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Test Database
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>