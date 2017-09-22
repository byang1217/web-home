//
//  ViewController.m
//  PhotoAlbum-Diary
//
//  Created by Bin Yang on 6/15/16.
//  Copyright © 2016 Bin Yang. All rights reserved.
//

#import "SetupView.h"
#import "ScanerVC.h"


enum {
    SETUP_CONFIG_VIDEOUPLOAD = 0,
    SETUP_CONFIG_WIFIONLY,
    SETUP_CONFIG_BGUPLOAD,
    SETUP_CONFIG_GPS,
    SETUP_CONFIG_HEALTH,
    SETUP_CONFIG_NUM
};


enum {
    SETUP_CONFIG = 0,
    SETUP_ALBUM,
    SETUP_NUM
};

@interface MySwitch : UISwitch
@property NSIndexPath *indexPath;
@end
@implementation MySwitch
@end


@interface SetupView ()
@property BOOL navigationBarHiddenSave;

@property PHFetchResult *smartAlbums;
@property PHFetchResult *userAlbums;

@property BOOL setup_is_allupload;
@property NSMutableArray *setup_sel_albums;

@end

@implementation SetupView

- (void)viewDidLoad {
    UIBarButtonItem *doneButton = [[UIBarButtonItem alloc] initWithTitle:@"Done" style:UIBarButtonItemStylePlain target:self action:@selector(doneSetup:)];
    self.navigationItem.leftBarButtonItem=doneButton;
    
    [super viewDidLoad];
}

-(void) viewWillAppear:(BOOL)animated {
    self.navigationBarHiddenSave = self.navigationController.navigationBarHidden;
    self.navigationController.navigationBarHidden = NO;
    
    self.setup_is_allupload = setup_get_bool(SETUP_KEY_IS_ALLUPLOAD);
    self.setup_sel_albums = setup_get_MArray(SETUP_KEY_SEL_ALBUMS);
    
    self.userAlbums = [PHCollectionList fetchTopLevelUserCollectionsWithOptions:nil];
    //    self.smartAlbums = [PHAssetCollection fetchAssetCollectionsWithType:PHAssetCollectionTypeSmartAlbum subtype:PHAssetCollectionSubtypeAlbumRegular options:nil];
    [self.tableView reloadData];

}

-(void) viewWillDisappear:(BOOL)animated {
    self.navigationController.navigationBarHidden = self.navigationBarHiddenSave;
    [super viewWillDisappear:animated];
}

#pragma mark - UITableViewDataSource

- (void)prepareForSegue:(UIStoryboardSegue *)segue sender:(id)sender {
    UITableViewCell *cell = sender;
    NSIndexPath *indexPath = [self.tableView indexPathForCell:cell];
    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;

    /*
    if (section == SETUP_USER && row == SETUP_USER_BIND) {
        ScanerVC *scanViewController = segue.destinationViewController;
        scanViewController.scanResultHandler = ^BOOL (NSString *str) {
            MyInf(@"scan result: %@", str);
            return YES;
        };
    }
     */
}

- (NSInteger)numberOfSectionsInTableView:(UITableView *)tableView {
    return SETUP_NUM;
}

- (NSInteger)tableView:(UITableView *)tableView numberOfRowsInSection:(NSInteger)section {
    NSInteger numberOfRows = 0;
    if (section == SETUP_CONFIG) {
        numberOfRows = SETUP_CONFIG_NUM;
    }
    if (section == SETUP_ALBUM) {
        numberOfRows = self.userAlbums.count + 1;
    }
    
    return numberOfRows;
}

- (UITableViewCell *)tableView:(UITableView *)tableView cellForRowAtIndexPath:(NSIndexPath *)indexPath {
    UITableViewCell *cell = nil;

    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;
    
    if (section == SETUP_ALBUM) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"SetupAlbum" forIndexPath:indexPath];
        
        MySwitch *switchView = [[MySwitch alloc] initWithFrame:CGRectZero];
        switchView.indexPath = indexPath;
        [switchView addTarget:self action:@selector(switchChanged:) forControlEvents:UIControlEventValueChanged];
        if (row == 0) {
            [switchView setOn:self.setup_is_allupload animated:NO];
            cell.textLabel.text = @"All";
            cell.accessoryView = switchView;
        }else {
            PHCollection *collection = self.userAlbums[indexPath.row - 1];
            cell.textLabel.text = collection.localizedTitle;
            
            if ([self.setup_sel_albums containsObject:collection.localIdentifier]) {
                [switchView setOn:YES animated:NO];
            }else {
                [switchView setOn:NO animated:NO];
            }

            cell.accessoryView = switchView;
            if (self.setup_is_allupload) {
                cell.userInteractionEnabled = NO;
                cell.textLabel.enabled = NO;
                switchView.enabled = NO;
            }else {
                cell.userInteractionEnabled = YES;
                cell.textLabel.enabled = YES;
                switchView.enabled = YES;
            }
        }
    }
    if (section == SETUP_CONFIG) {
        cell = [tableView dequeueReusableCellWithIdentifier:@"SetupConfig" forIndexPath:indexPath];
        MySwitch *switchView = [[MySwitch alloc] initWithFrame:CGRectZero];
        switchView.indexPath = indexPath;
        [switchView addTarget:self action:@selector(switchChanged:) forControlEvents:UIControlEventValueChanged];
        if (row == SETUP_CONFIG_VIDEOUPLOAD) {
            [switchView setOn:setup_get_bool(SETUP_KEY_IS_VIDEOUPLOAD) animated:NO];
            cell.textLabel.text = @"Upload Video";
        }
        if (row == SETUP_CONFIG_WIFIONLY) {
            [switchView setOn:setup_get_bool(SETUP_KEY_IS_WIFIUPLOAD) animated:NO];
            cell.textLabel.text = @"upload with 3G/4G";
        }
        if (row == SETUP_CONFIG_BGUPLOAD) {
            [switchView setOn:setup_get_bool(SETUP_KEY_IS_BGUPLOAD) animated:NO];
            cell.textLabel.text = @"Enable background upload";
        }
        if (row == SETUP_CONFIG_GPS) {
            [switchView setOn:setup_get_bool(SETUP_KEY_IS_GPSUPLOAD) animated:NO];
            cell.textLabel.text = @"Upload Location Data";
        }
        if (row == SETUP_CONFIG_HEALTH) {
            [switchView setOn:setup_get_bool(SETUP_KEY_IS_HEALTHUPLOAD) animated:NO];
            cell.textLabel.text = @"Upload Health Data";
        }
        cell.accessoryView = switchView;
    }

    return cell;
}

- (NSString *)tableView:(UITableView *)tableView titleForHeaderInSection:(NSInteger)section {
    if (section == SETUP_ALBUM)
        return @"Albums for Upload";
    return @"";
}

/* event callback functions */
- (void) tableView: (UITableView *) tableView didSelectRowAtIndexPath: (NSIndexPath *) indexPath {
    MyInf(@"click me: %@", indexPath);
/*
    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;
 */
}

- (void) switchChanged:(id)sender {
    MySwitch *switchControl = sender;
    BOOL on = switchControl.on;
    NSIndexPath *indexPath = switchControl.indexPath;
    NSInteger section = indexPath.section;
    NSInteger row = indexPath.row;

    if (section == SETUP_ALBUM) {
        if (row == 0) {
            self.setup_is_allupload = switchControl.on;
            setup_set_bool(SETUP_KEY_IS_ALLUPLOAD, self.setup_is_allupload);
            [self.tableView reloadData];
        }else {
            PHCollection *collection = self.userAlbums[row - 1];
            MyInf(@"collection id: %@", collection.localIdentifier);
            if (on) {
                if (![self.setup_sel_albums containsObject:collection.localIdentifier])
                    [self.setup_sel_albums addObject:collection.localIdentifier];
            }else {
                if ([self.setup_sel_albums containsObject:collection.localIdentifier])
                    [self.setup_sel_albums removeObject:collection.localIdentifier];
            }
        }
        setup_set(SETUP_KEY_SEL_ALBUMS, self.setup_sel_albums);
    }
    
    if (section == SETUP_CONFIG) {
        if (row == SETUP_CONFIG_VIDEOUPLOAD) {
            setup_set_bool(SETUP_KEY_IS_VIDEOUPLOAD, on);
        }
        if (row == SETUP_CONFIG_WIFIONLY) {
            setup_set_bool(SETUP_KEY_IS_WIFIUPLOAD, on);
        }
        if (row == SETUP_CONFIG_BGUPLOAD) {
            setup_set_bool(SETUP_KEY_IS_BGUPLOAD, on);
        }
        if (row == SETUP_CONFIG_GPS) {
            setup_set_bool(SETUP_KEY_IS_GPSUPLOAD, on);
        }
        if (row == SETUP_CONFIG_HEALTH) {
            setup_set_bool(SETUP_KEY_IS_HEALTHUPLOAD, on);
        }
    }

    AlbumSync_reset();
}

-(void)doneSetup:(UIBarButtonItem *)sender {
    [self.navigationController popToRootViewControllerAnimated:YES];
}

@end

/* C functions */

id setup_get(NSString *key)
{
    return [[NSUserDefaults standardUserDefaults] objectForKey:key];
}

void setup_set(NSString *key, id obj)
{
    NSUserDefaults *userDefaults = [NSUserDefaults standardUserDefaults];
    if (obj)
        [userDefaults setObject:obj forKey:key];
    [userDefaults synchronize];
}

NSString *setup_get_string(NSString *key)
{
    NSString *str = setup_get(key);
    if (!str)
        str = @"";
    return str;
}

NSMutableArray *setup_get_MArray(NSString *key)
{
    NSMutableArray *ma = [[NSMutableArray alloc] init];
    NSArray *a = setup_get(key);
    
    if (a)
        [ma addObjectsFromArray:a];
    return ma;
}

BOOL setup_get_bool(NSString *key)
{
    NSNumber *num = setup_get(key);
    if (num) {
        return [num boolValue];
    }else {
        return NO;
    }
}

void setup_set_bool(NSString *key, BOOL value)
{
    NSNumber *num = [[NSNumber alloc] initWithBool:value];
    setup_set(key, num);
}

