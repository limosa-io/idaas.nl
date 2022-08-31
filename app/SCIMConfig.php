<?php

namespace App;

use ArieTimmerman\Laravel\SCIMServer\SCIM\Schema;
use ArieTimmerman\Laravel\SCIMServer\Attribute\AttributeMapping;
use App\User;
use Illuminate\Support\Facades\Hash;
use App\Role;
use App\Link;
use App\Subject;
use ArieTimmerman\Laravel\SCIMServer\Exceptions\SCIMException;
use App\Group;
use ParagonIE\ConstantTime\Base32;

class SCIMConfig extends \ArieTimmerman\Laravel\SCIMServer\SCIMConfig
{
    public function getUserConfig()
    {
        return [

            // Set to 'null' to make use of auth.providers.users.model (App\User::class)
            'class' => User::class,
            'singular' => 'User',
            'validations' => [

                'urn:ietf:params:scim:schemas:core:2.0:User:userName' => 'nullable|min:3|unique:users,name,[OBJECT_ID]',
                'urn:ietf:params:scim:schemas:core:2.0:User:active' => 'nullable|boolean',
                'urn:ietf:params:scim:schemas:core:2.0:User:emails' => ['nullable', 'array'],

                'urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers' => ['nullable', 'array'],
                'urn:ietf:params:scim:schemas:core:2.0:User:addresses' => ['nullable', 'array'],
                'urn:ietf:params:scim:schemas:core:2.0:User:displayName' => 'nullable',

                'urn:ietf:params:scim:schemas:core:2.0:User:preferredLanguage' => 'nullable|max:15',

                // Note the notation of '2___0'
                'urn:ietf:params:scim:schemas:core:2.0:User:emails.*.value' => 'nullable|email|required_without:urn:ietf:params:scim:schemas:core:2___0:User:userName|unique:users,email,[OBJECT_ID]',

                'urn:ietf:params:scim:schemas:core:2.0:User:phoneNumbers.*.value' => 'nullable',
                'urn:ietf:params:scim:schemas:core:2.0:User:addresses.*.formatted' => 'nullable',

                'urn:ietf:params:scim:schemas:core:2.0:User:name.givenName' => 'nullable',
                'urn:ietf:params:scim:schemas:core:2.0:User:name.familyName' => 'nullable',

                'urn:ietf:params:scim:schemas:core:2.0:User:password' => 'nullable|min:1|max:200',

                // 'urn:ietf:params:scim:schemas:core:2.0:User:roles' => 'nullable|array',
                'urn:ietf:params:scim:schemas:core:2.0:User:roles.*.value' => ['required', function ($attribute, $value, $fail) {
                    if (Role::find($value) == null) {
                        return $fail($attribute . ' is not a valid role.');
                    }
                }],


                'arietimmerman:ice:metadataUser' => 'nullable|json',
                'arietimmerman:ice:extraIdentifier1' => 'nullable',



                'arietimmerman:ice:links' => 'nullable|array',
                'arietimmerman:ice:links.*.value' => 'required',
                'arietimmerman:ice:links.*.value' => function ($attribute, $value, $fail) {
                    // TODO: ensure the link is not already linked to an user

                    if (Link::find($value) == null) {
                        return $fail($attribute . ' is not a valid link.');
                    }
                },

                'urn:ietf:params:scim:schemas:core:2.0:User:photos' => 'nullable',
                'urn:ietf:params:scim:schemas:core:2.0:User:photos.*.value' => 'nullable',
                'arietimmerman:ice:otpSecret' => ['nullable', function ($attribute, $value, $fail) {
                    $valid = true;
                    try {
                        Base32::decode(strtolower($value));
                    } catch (\RangeException $e) {
                        $valid = false;
                    }

                    if (!$valid) {
                        return $fail($attribute . ' is not a Base32 encoded string. Received: ' . $value);
                    }
                }]
            ],

            'validations.create' => [
                'urn:ietf:params:scim:schemas:core:2.0:User:password' => 'required',
            ],

            'schema' => [Schema::SCHEMA_USER, 'arietimmerman:ice', 'urn:ietf:params:scim:schemas:extension:account:2.0:Password'],
            'withRelations' => ['links', 'roles', 'groups'],
            'map_unmapped' => true,
            'unmapped_namespace' => 'urn:ietf:params:scim:schemas:laravel:unmapped',
            'description' => 'User Account',

            // Map a SCIM attribute to an attribute of the object.
            'mapping' => [

                'id' => AttributeMapping::eloquent("id")->disableWrite(),

                'externalId' => null,

                'meta' => [
                    'created' => AttributeMapping::eloquent("created_at")->disableWrite(),
                    'lastModified' => AttributeMapping::eloquent("updated_at")->disableWrite(),

                    'location' => (new AttributeMapping())->setRead(
                        function ($object) {
                            return route(
                                'scim.resource',
                                [
                                'resourceType' => 'Users',
                                'resourceObject' => $object->id
                                ]
                            );
                        }
                    )->disableWrite(),

                    'resourceType' => AttributeMapping::constant("User")
                ],

                'schemas' => AttributeMapping::constant(
                    [
                    'urn:ietf:params:scim:schemas:core:2.0:User',
                    'arietimmerman:ice',
                    ]
                )->ignoreWrite(),

                'urn:ietf:params:scim:schemas:extension:account:2.0:Password' => [
                    'lastSuccessfulLoginDate' => AttributeMapping::eloquent("last_successful_login_date")->disableWrite()
                ],

                'arietimmerman:ice' => [

                    'links' => AttributeMapping::eloquent("link")->setRead(
                        function ($object) {
                            $result = [];

                            foreach (($object->links ?? []) as $link) {
                                $result[] = $link->toArray();
                            }

                            return $result;
                        }
                    )->ignoreWrite()->setReplace(
                        function ($value, &$object) {
                            $ids = collect($value)->pluck('id');

                            foreach ($object->links as $key => $link) {
                                if (!$ids->contains($link->id)) {
                                    $link->delete();
                                    unset($object->links[$key]);
                                }
                            }

                            $newSubjectIds = collect($value)->pluck('subject_id');
                            $existingSubjectIds = $object->links->pluck('subject_id');

                            foreach ($newSubjectIds->all() as $subjectId) {
                                if (!$existingSubjectIds->contains($subjectId)) {
                                    $subject = Subject::where(['identifier' => $subjectId])->first();

                                    if ($subject == null) {
                                        throw new SCIMException('Unknown subject', 500);
                                    }

                                    $type = strtok($subjectId, '|');

                                    $link = Link::create(
                                        [

                                        'user_id' => $object->id,
                                        'subject_type' => $type,
                                        'subject_module' => null,
                                        'subject_id' => $subjectId

                                        ]
                                    );

                                    $object->links[] = $link;
                                }
                            }
                        }
                    ),

                    'extraIdentifier1' => AttributeMapping::eloquent("extraIdentifier1"),
                    'extraIdentifier2' => AttributeMapping::eloquent("extraIdentifier2"),
                    'extraIdentifier3' => AttributeMapping::eloquent("extraIdentifier3"),
                    'extraIdentifier4' => AttributeMapping::eloquent("extraIdentifier4"),

                    'metadataUser' => AttributeMapping::eloquent("metadataUser"),

                    'gender' => AttributeMapping::eloquent("gender"),

                    'birthDate' => AttributeMapping::eloquent("birthDate"),

                    'timezone' => AttributeMapping::eloquent("timezone"),

                    'otpSecret' => AttributeMapping::eloquent("otp_secret")->disableRead(),

                    'otpSecretPresent' => AttributeMapping::constant('otp_secret_present')->setRead(
                        function (&$object) {
                            return $object->otp_secret ? true : false;
                        }
                    )->disableWrite(),

                    'user_metadata' => AttributeMapping::eloquent("user_metadata"),
                    'app_metadata' => AttributeMapping::eloquent("app_metadata"),

                ],

                'urn:ietf:params:scim:schemas:core:2.0:User' => [

                    'userName' => AttributeMapping::eloquent("name"),

                    'name' => [
                        'formatted' => AttributeMapping::eloquent("formattedName"),
                        'familyName' => AttributeMapping::eloquent("familyName"),
                        'givenName' => AttributeMapping::eloquent("givenName"),
                        'middleName' => AttributeMapping::eloquent("middleName"),
                        'honorificPrefix' => null,
                        'honorificSuffix' => null
                    ],

                    'displayName' => AttributeMapping::eloquent("displayName"),
                    'nickName' => null,
                    'profileUrl' => null,
                    'title' => null,
                    'userType' => null,
                    'preferredLanguage' => AttributeMapping::eloquent("preferredLanguage"), // Section 5.3.5 of [RFC7231]
                    'locale' => null, // see RFC5646
                    'timezone' => null, // see RFC6557
                    'timezone' => null,
                    'active' => AttributeMapping::eloquent("active"),

                    'password' => AttributeMapping::eloquent('password')->disableRead()->setAdd(
                        function ($value, &$object) {
                            $object->password = Hash::make($value);
                        }
                    )->setReplace(
                        function ($value, &$object) {
                            $object->password = Hash::make($value);
                        }
                    ),

                    // Multi-Valued Attributes
                    'emails' => [[
                        "value" => AttributeMapping::eloquent("email"),
                        "display" => null,
                        "type" => AttributeMapping::constant("other")->ignoreWrite(),
                        "primary" => AttributeMapping::constant(true)->ignoreWrite()
                    ]],

                    'phoneNumbers' => [[
                        "value" => AttributeMapping::eloquent("phoneNumber"),
                        "display" => null,
                        "type" => AttributeMapping::constant("other")->ignoreWrite(),
                        "primary" => AttributeMapping::constant(true)->ignoreWrite()
                    ]],

                    'addresses' => [[
                        "formatted" => AttributeMapping::eloquent("address"),
                        "type" => AttributeMapping::constant("other")->ignoreWrite(),
                        "primary" => AttributeMapping::constant(true)->ignoreWrite()
                    ]],

                    'ims' => [[
                        "value" => null,
                        "display" => null,
                        "type" => null,
                        "primary" => null
                    ]], // Instant messaging addresses for the User

                    'photos' => [[
                        "value" => AttributeMapping::eloquent("picture"),
                        "type" => AttributeMapping::constant("thumbnail")->ignoreWrite()
                    ]],

                    'entitlements' => null,
                    'roles' => AttributeMapping::eloquentCollection('roles'),

                    'groups' => AttributeMapping::eloquent("groups")->setRead(
                        function ($object) {
                            $result = [];

                            foreach ($object->groups as $group) {
                                $result[] = [
                                'value' => $group->id,
                                '$ref' => route(
                                    'scim.resource',
                                    [
                                    'resourceType' => 'Group',
                                    'resourceObject' => $group->id
                                    ]
                                ),
                                    'display' => $group->displayName ?? $group->name
                                ];
                            }

                            return $result;
                        }
                    )->ignoreWrite()->setGetSubNode(
                        function ($key, $schema = null) {
                            if ($key == 'value') {
                                return AttributeMapping::eloquent('groups.id')->ignoreRead()->ignoreWrite()->setRelationship('groups');
                            }

                            return null;
                        }
                    ),

                    'x509Certificates' => null
                ],

            ]
            ];
    }

    public function getGroupsConfig()
    {
        return [
            'class' => Group::class,

            'withRelations' => ['members'],
            'singular' => 'Group',
            'schema' => ['urn:ietf:params:scim:schemas:core:2.0:Group'],

            'validations' => [
                'urn:ietf:params:scim:schemas:core:2.0:Group:displayName' => 'nullable',
                'urn:ietf:params:scim:schemas:core:2.0:Group:name' => 'required|min:3|unique:groups,name,[OBJECT_ID]',
                'urn:ietf:params:scim:schemas:core:2.0:Group:members' => 'nullable|array',

                // Check for existing in the functions itself. More effecient due to 'whereIn' searches
                'urn:ietf:params:scim:schemas:core:2.0:Group:members.*.value' => 'required'
            ],

            'mapping' => [

                'id' => AttributeMapping::eloquent("id")->disableWrite(),



                'meta' => [
                    'created' => AttributeMapping::eloquent("created_at")->disableWrite(),
                    'lastModified' => AttributeMapping::eloquent("updated_at")->disableWrite(),

                    'location' => (new AttributeMapping())->setRead(
                        function ($object) {
                            return route(
                                'scim.resource',
                                [
                                'resourceType' => 'Groups',
                                'resourceObject' => $object->id
                                ]
                            );
                        }
                    )->disableWrite(),

                    'resourceType' => AttributeMapping::constant("Group")
                ],

                'schemas' => AttributeMapping::constant(
                    [
                    'urn:ietf:params:scim:schemas:core:2.0:Group',
                    ]
                )->ignoreWrite(),

                'urn:ietf:params:scim:schemas:core:2.0:Group' => [
                    'name' => AttributeMapping::eloquent("name"),
                    'displayName' => AttributeMapping::eloquent("displayName"),
                    'members' => AttributeMapping::eloquent("members")->setRead(
                        function ($object) {
                            $attributes = explode(',', request()->input('attributes') ?? '');

                            if (!request()->input('attributes') || !in_array('members', $attributes)) {
                                return null;
                            }

                            $result = [];

                            foreach ($object->members as $member) {
                                $result[] = [
                                'value' => $member->id,
                                '$ref' => route(
                                    'scim.resource',
                                    [
                                    'resourceType' => 'Users',
                                    'resourceObject' => $member->id
                                    ]
                                ),
                                    'display' => $member->name ?? $member->email
                                ];
                            }

                            return $result;
                        }
                    )->ignoreWrite()->setAdd(
                        function ($value, &$object) {
                            // $values = collect($value)->pluck('value');
                            // $existingUsers = User::whereIn('id', $values->all())->select('id')->get()->pluck('id');

                            $existingUsers = User::whereIn('id', [$value])->select('id')->get()->pluck('id');

                            if (($diff = collect($value)->diff($existingUsers))->count() > 0) {
                                throw new SCIMException(sprintf('One or more members are unknown: %s', implode(',', $diff->all())), 500);
                            }

                            $object->members()->syncWithoutDetaching($existingUsers->all());

                            $object->load('members');
                        }
                    )->setRemove(
                        function ($value, &$object) {
                            $values = collect($value)->pluck('value');

                            $object->members()->detach($values->all());
                            $object->load('members');
                        }
                    )->setReplace(
                        function ($value, &$object) {
                            $values = collect($value)->pluck('value');

                            $existingUsers = User::whereIn('id', $values->all())->select('id')->get()->pluck('id');

                            if (($diff = $values->diff($existingUsers))->count() > 0) {
                                throw new SCIMException('One or more members are unknown', 500);
                            }

                            $object->members()->sync($existingUsers->all());
                        }
                    )
                ]

            ]

                ];
    }

    public function getRolesConfig()
    {
        return [

            'class' => Role::class,
            'singular' => 'Role',
            'map_unmapped' => true,

            'withRelations' => ['tenant'],

            'schema' => ['arietimmerman:ice:Role']

        ];
    }

    public function getSubjectConfig()
    {
        return [

            'class' => Subject::class,
            'singular' => 'Subject',
            'map_unmapped' => true,

            'unmapped_namespace' => 'urn:ietf:params:scim:schemas:subjects',

            'withRelations' => ['tenant','user:id,email'],

            'schema' => ['urn:ietf:params:scim:schemas:subjects'],

            'mapping' => [

                'id' => AttributeMapping::eloquent("id")->disableWrite(),

                'externalId' => null,

                'meta' => [
                    'created' => AttributeMapping::eloquent("created_at")->disableWrite(),
                    'lastModified' => AttributeMapping::eloquent("updated_at")->disableWrite(),

                    'location' => (new AttributeMapping())->setRead(
                        function ($object) {
                            return route(
                                'scim.resource',
                                [
                                'resourceType' => 'Subjects',
                                'resourceObject' => $object->id
                                ]
                            );
                        }
                    )->disableWrite(),

                    'resourceType' => AttributeMapping::constant("Subject")
                ],

                'urn:ietf:params:scim:schemas:subjects' => [

                    'identifier' => AttributeMapping::eloquent("identifier"),

                    // 'user' => [
                    //     'id' => AttributeMapping::eloquent("user.id"),
                    //     'mail' => AttributeMapping::eloquent("user.email"),
                    // ]

                ]
            ]

        ];
    }

    public function getConfig()
    {
        return [
            'Users' => $this->getUserConfig(),
            'Roles' => $this->getRolesConfig(),
            'Groups' => $this->getGroupsConfig(),
            'Subjects' => $this->getSubjectConfig(),
        ];
    }
}
