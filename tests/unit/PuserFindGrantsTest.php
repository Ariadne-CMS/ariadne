<?php

require_once AriadneBasePath . '/objects/puser.phtml';

class PuserFindGrantsTest extends \PHPUnit\Framework\TestCase
{
	private function createUserWithGrants(array $grants): puser
	{
		$data = new baseObject();
		$data->config = new baseObject();
		$data->config->usergrants = [
			'/content/section/' => $grants,
		];

		$store = new class {
			public function make_path($currentPath, $relativePath)
			{
				if ($relativePath !== '..') {
					throw new InvalidArgumentException('This test store only supports parent paths.');
				}

				$currentPath = rtrim($currentPath, '/');
				$separator = strrpos($currentPath, '/');

				return substr($currentPath, 0, $separator + 1) ?: '/';
			}
		};

		$user = new puser();
		$user->init($store, '/system/users/test/', $data);

		return $user;
	}

	private function grantsForEveryScope(): array
	{
		return [
			'local' => ARGRANTLOCAL,
			'children' => ARGRANTCHILDREN,
			'global' => ARGRANTGLOBAL,
			'modified' => [
				'local' => ARGRANTLOCAL,
				'children' => ARGRANTCHILDREN,
				'global' => ARGRANTGLOBAL,
			],
		];
	}

	public function testCurrentPathUsesLocalAndGlobalGrants(): void
	{
		$user = $this->createUserWithGrants($this->grantsForEveryScope());
		$result = [];

		$setBy = $user->FindGrants('/content/section/', $result);

		$this->assertSame('/content/section/', $setBy);
		$this->assertSame([
			'local' => ARGRANTLOCAL,
			'global' => ARGRANTGLOBAL,
			'modified' => [
				'local' => ARGRANTLOCAL,
				'global' => ARGRANTGLOBAL,
			],
		], $result);
	}

	public function testDescendantUsesChildrenAndGlobalGrants(): void
	{
		$user = $this->createUserWithGrants($this->grantsForEveryScope());
		$result = [];

		$setBy = $user->FindGrants('/content/section/page/', $result);

		$this->assertSame('/content/section/', $setBy);
		$this->assertSame([
			'children' => ARGRANTCHILDREN,
			'global' => ARGRANTGLOBAL,
			'modified' => [
				'children' => ARGRANTCHILDREN,
				'global' => ARGRANTGLOBAL,
			],
		], $result);
	}

	public function testCachedDescendantGrantsKeepTheirScope(): void
	{
		$user = $this->createUserWithGrants($this->grantsForEveryScope());
		$result = [];
		$user->FindGrants('/content/section/page/', $result);

		$result = [];
		$setBy = $user->FindGrants('/content/section/page/', $result);

		$this->assertSame('/content/section/', $setBy);
		$this->assertSame([
			'children' => ARGRANTCHILDREN,
			'global' => ARGRANTGLOBAL,
			'modified' => [
				'children' => ARGRANTCHILDREN,
				'global' => ARGRANTGLOBAL,
			],
		], $result);
	}
}
