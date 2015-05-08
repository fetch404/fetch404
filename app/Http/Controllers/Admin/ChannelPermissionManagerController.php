<?php namespace App\Http\Controllers\Admin;

use App\Channel;
use App\ChannelPermission;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Http\Requests\Admin\Forum\UpdateChannelPermissionsRequest;
use App\Role;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class ChannelPermissionManagerController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
		$channels = Channel::all();

		return view('core.admin.forums.permission-editor.channel.index', array(
			'channels' => $channels
		));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param Channel $channel
	 * @return Response
	 */
	public function edit(Channel $channel)
	{
		//
		$groups = Role::lists('name', 'id');

		$createThreadIds = ChannelPermission::where('channel_id', '=', $channel->id)->where('permission_id', '=', 1)->lists('role_id', 'role_id');
		$accessChannelIds = ChannelPermission::where('channel_id', '=', $channel->id)->where('permission_id', '=', 21)->lists('role_id', 'role_id');
		$replyIds = ChannelPermission::where('channel_id', '=', $channel->id)->where('permission_id', '=', 6)->lists('role_id', 'role_id');

		return view('core.admin.forums.permission-editor.channel.edit', array(
			'channel' => $channel,
			'accessChannel' => $accessChannelIds,
			'createThread' => $createThreadIds,
			'reply' => $replyIds,
			'groups' => $groups
		));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param UpdateChannelPermissionsRequest $request
	 * @return Response
	 */
	public function update(UpdateChannelPermissionsRequest $request)
	{
		//
		$channel = $request->route()->getParameter('channel');

		$accessChannel = $request->input('allowed_groups');
		$createThreads = $request->input('create_threads');
		$reply = $request->input('reply_to_threads');

		ChannelPermission::where('channel_id', '=', $channel->id)
			->where('permission_id', '=', 21)
			->orWhere('permission_id', '=', 1)
			->orWhere('permission_id', '=', 6)
			->delete();

		foreach($accessChannel as $id)
		{
			$perm = ChannelPermission::firstOrCreate(array(
				'permission_id' => 21,
				'role_id' => $id,
				'channel_id' => $channel->id
			));
		}

		foreach($createThreads as $id)
		{
			$create_threads = ChannelPermission::firstOrCreate(array(
				'permission_id' => 1,
				'role_id' => $id,
				'channel_id' => $channel->id
			));
		}

		foreach($reply as $id)
		{
			$replyToThread = ChannelPermission::firstOrCreate(array(
				'permission_id' => 6,
				'role_id' => $id,
				'channel_id' => $channel->id
			));
		}

		Flash::success('Updated channel permissions!');

		return redirect(route('admin.forum.get.permissions.channels.edit', array(
			$channel
		)));
	}

}
