//
//  ViewController.h
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import <UIKit/UIKit.h>
#import "AlbumSync.h"

@interface SetupView : UITableViewController

@end

#define MY_VERSION              1001

#define SETUP_KEY_UPLOAD_EN     @"setup_upload_enable"

#define SETUP_KEY_SERVER_URL    @"setup_server_url"
#define SETUP_KEY_USER_ID       @"setup_user_id"
#define SETUP_KEY_USER_TOKEN    @"setup_user_token"
#define SETUP_KEY_USER_NAME     @"setup_user_name"
#define SETUP_KEY_DEV_ID        @"setup_dev_id"

#define SETUP_KEY_IS_BIND       @"setup_is_bind"
#define SETUP_KEY_IS_BGUPLOAD   @"setup_is_bgupload"
#define SETUP_KEY_IS_WIFIUPLOAD @"setup_is_wifiupload"
#define SETUP_KEY_IS_HEALTHUPLOAD @"setup_is_healthupload"
#define SETUP_KEY_IS_GPSUPLOAD @"setup_is_gpsupload"
#define SETUP_KEY_IS_VIDEOUPLOAD @"setup_is_videoupload"

#define SETUP_KEY_IS_ALLUPLOAD  @"setup_is_allupload"
#define SETUP_KEY_SEL_ALBUMS    @"setup_sel_albums"

#define SAVE_KEY_UPLOAD_ASSET_TODO   @"SAVE_KEY_UPLOAD_ASSET_TODO"



id setup_get(NSString *key);
void setup_set(NSString *key, id obj);
NSString *setup_get_string(NSString *key);
void setup_set_string(NSString *key, NSString *str);
void setup_set_bool(NSString *key, BOOL value);
BOOL setup_get_bool(NSString *key);
NSMutableArray *setup_get_MArray(NSString *key);
